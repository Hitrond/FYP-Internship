<?php

namespace App\Http\Controllers;

use App\Mail\SupervisorWelcomeMail;
use App\Models\FinalClearance;
use App\Models\InternshipCycle;
use App\Models\PlacementClearance;
use App\Models\User;
use App\Services\BrevoTransactionalMailer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminClearanceController extends Controller
{
    public function index(Request $request)
    {
        [$cycles, $selectedCycle] = $this->cycleSelection($request);
        $students = $this->studentProgressQuery($request)
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $studentIds = $selectedCycle?->assignments()->pluck('student_id');
        $summary = [
            'total' => $studentIds?->count() ?? User::where('role', 'student')->count(),
            'active' => User::where('role', 'student')
                ->when($studentIds, fn ($query) => $query->whereIn('id', $studentIds))
                ->whereHas('placementClearances', fn ($query) => $query
                    ->whereIn('status', ['approved', 'completed'])
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)))
                ->count(),
            'final_pending' => FinalClearance::where('status', FinalClearance::STATUS_PENDING)
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->count(),
            'completed' => FinalClearance::where('status', FinalClearance::STATUS_COMPLETED)
                ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
                ->count(),
        ];

        return view('admin.clearances.index', compact('students', 'summary', 'cycles', 'selectedCycle'));
    }

    public function export(Request $request): StreamedResponse
    {
        $students = $this->studentProgressQuery($request)->orderBy('name')->get();
        $filename = 'internship-cohort-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($students): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Student Name',
                'University ID',
                'Email',
                'Academic Mentor',
                'Industrial Supervisor',
                'Company',
                'Start Date',
                'End Date',
                'Placement Status',
                'Approved Logbooks',
                'Timeline Weeks',
                'Final Clearance',
                'Supervisor Score',
                'Final Result',
            ]);

            foreach ($students as $student) {
                $placement = $student->latestPlacementClearance;

                fputcsv($handle, [
                    $student->name,
                    $student->profile?->tp_number,
                    $student->email,
                    $student->mentor?->name ?? 'Unassigned',
                    $student->supervisor?->name ?? 'Unassigned',
                    $placement?->company_name,
                    $placement?->start_date?->format('Y-m-d'),
                    $placement?->end_date?->format('Y-m-d'),
                    $placement?->status ?? 'Not submitted',
                    $student->approved_logbooks_count,
                    $student->generated_logbooks_count,
                    $student->finalClearance?->status ?? 'Not submitted',
                    $student->internshipResult?->supervisor_score,
                    $student->internshipResult
                        ? ucfirst($student->internshipResult->result)
                        : 'Pending',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Retained for older approved placements that were created before automatic
     * Industrial Supervisor provisioning moved to Academic Mentor approval.
     */
    public function generateSupervisorAccount(
        Request $request,
        int $id,
        BrevoTransactionalMailer $brevoMailer
    ) {
        $clearance = PlacementClearance::with('student')->findOrFail($id);

        abort_unless(in_array($clearance->status, ['approved', 'completed'], true), 422);
        abort_if(blank($clearance->supervisor_personal_email), 422, 'A supervisor personal email is required.');

        $message = DB::transaction(function () use ($clearance, $brevoMailer): string {
            $supervisor = User::where('email', $clearance->supervisor_personal_email)->first();
            $rawPassword = Str::password(12);

            if (! $supervisor) {
                $supervisor = User::create([
                    'name' => $clearance->supervisor_name,
                    'email' => $clearance->supervisor_personal_email,
                    'password' => Hash::make($rawPassword),
                    'role' => 'supervisor',
                ]);
            }

            abort_unless($supervisor->isSupervisor(), 422, 'That email belongs to a non-supervisor account.');

            $supervisor->forceFill([
                'name' => $clearance->supervisor_name ?: $supervisor->name,
                'password' => Hash::make($rawPassword),
                'email_verified_at' => $supervisor->email_verified_at ?? now(),
            ])->save();

            $supervisor->profile()->updateOrCreate(
                ['user_id' => $supervisor->id],
                [
                    'company_email' => $clearance->supervisor_email,
                    'company_name' => $clearance->company_name,
                    'company_address' => $clearance->office_address,
                ]
            );

            $clearance->student->update(['supervisor_id' => $supervisor->id]);
            $clearance->update([
                'supervisor_user_id' => $supervisor->id,
                'status' => 'completed',
            ]);

            try {
                if ($brevoMailer->configured() && ! app()->runningUnitTests()) {
                    $brevoMailer->sendSupervisorWelcome($supervisor, $rawPassword, $clearance->student->name);
                } else {
                    Mail::to($supervisor->email)->send(
                        new SupervisorWelcomeMail($supervisor, $rawPassword, $clearance->student->name)
                    );
                }

                return 'Industrial Supervisor login email sent to '.$supervisor->email.'.';
            } catch (\Throwable $exception) {
                report($exception);

                return 'Supervisor account saved, but the email could not be sent. Temporary password: '.$rawPassword;
            }
        });

        return back()->with('success', $message);
    }

    private function studentProgressQuery(Request $request): Builder
    {
        [, $selectedCycle] = $this->cycleSelection($request);

        return User::query()
            ->where('role', 'student')
            ->when($selectedCycle, fn (Builder $query) => $query->whereHas(
                'cycleAssignments',
                fn ($assignment) => $assignment->where('internship_cycle_id', $selectedCycle->id)
            ))
            ->with([
                'profile',
                'mentor',
                'supervisor',
                'latestPlacementClearance' => fn ($query) => $query
                    ->with('cycle')
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)),
                'finalClearance' => fn ($query) => $query
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)),
                'internshipResult' => fn ($query) => $query
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)),
            ])
            ->withCount([
                'logbooks as approved_logbooks_count' => fn ($query) => $query
                    ->where('status', 'approved')
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)),
                'logbooks as generated_logbooks_count' => fn ($query) => $query
                    ->where('timeline_generated', true)
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id)),
            ])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->input('placement') === 'unassigned', fn (Builder $query) => $query->whereNull('mentor_id'))
            ->when($request->input('placement') === 'not_submitted', fn (Builder $query) => $query->whereDoesntHave(
                'placementClearances',
                fn ($placement) => $placement
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ))
            ->when($request->input('placement') === 'pending', fn (Builder $query) => $query->whereHas(
                'placementClearances',
                fn ($placement) => $placement
                    ->where('status', 'pending')
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ))
            ->when($request->input('placement') === 'active', fn (Builder $query) => $query->whereHas(
                'placementClearances',
                fn ($placement) => $placement
                    ->whereIn('status', ['approved', 'completed'])
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ))
            ->when(in_array($request->input('result'), ['pass', 'fail'], true), fn (Builder $query) => $query->whereHas(
                'internshipResult',
                fn ($result) => $result
                    ->where('result', $request->input('result'))
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ))
            ->when($request->input('result') === 'pending', fn (Builder $query) => $query->whereDoesntHave(
                'internshipResult',
                fn ($result) => $result
                    ->when($selectedCycle, fn ($query) => $query->where('internship_cycle_id', $selectedCycle->id))
            ));
    }

    private function cycleSelection(Request $request): array
    {
        $cycles = InternshipCycle::latest('placement_window_start')->get();
        $selectedCycle = $request->filled('semester')
            ? $cycles->firstWhere('id', (int) $request->input('semester'))
            : ($cycles->firstWhere('status', InternshipCycle::STATUS_ACTIVE) ?? $cycles->first());

        return [$cycles, $selectedCycle];
    }
}
