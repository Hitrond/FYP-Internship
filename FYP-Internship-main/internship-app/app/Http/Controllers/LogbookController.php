<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\InternshipCycle;
use App\Notifications\WorkflowAlertNotification;
use App\Services\PlacementTimelineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class LogbookController extends Controller
{
    public function mentorIndex(Request $request, PlacementTimelineService $timeline)
    {
        $mentor = $request->user();
        $cycles = InternshipCycle::whereHas('assignments', fn ($query) => $query->where('mentor_id', $mentor->id))
            ->latest('placement_window_start')
            ->get();
        $activeCycle = $request->filled('semester')
            ? $cycles->firstWhere('id', $request->integer('semester'))
            : InternshipCycle::active();

        $students = $mentor->assignedStudents()
            ->when($activeCycle, fn ($query) => $query->whereHas('cycleAssignments', fn ($assignment) => $assignment
                ->where('internship_cycle_id', $activeCycle->id)
                ->where('mentor_id', $mentor->id)))
            ->with(['profile', 'logbooks' => fn ($query) => $query
                ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
                ->orderBy('week_number')])
            ->orderBy('name')
            ->get();

        $students->each(fn ($student) => $student->logbooks->each(fn (Logbook $logbook) => $timeline->sync($logbook)));

        $selectedStudentId = $request->filled('student') ? $request->integer('student') : null;
        $status = in_array($request->input('status'), ['approved', 'pending', 'rejected', 'overdue_locked', 'open', 'scheduled'], true)
            ? $request->input('status')
            : null;

        return view('mentor.logbooks.index', compact('students', 'cycles', 'activeCycle', 'selectedStudentId', 'status'));
    }

    public function index(PlacementTimelineService $timeline)
    {
        $user = Auth::user();
        $logbooks = $user->logbooks()->get()
            ->each(fn (Logbook $logbook) => $timeline->sync($logbook))
            ->keyBy('week_number');
        $totalWeeks = $this->totalWeeksFor($user);

        return view('student.logbook.index', compact('logbooks', 'totalWeeks'));
    }

    public function create(Request $request, PlacementTimelineService $timeline)
    {
        $week = (int) $request->integer('week');
        abort_unless($week >= 1 && $week <= $this->totalWeeksFor($request->user()), 404);

        $timelineLogbook = $request->user()->logbooks()
            ->where('week_number', $week)
            ->first();

        if ($timelineLogbook) {
            $timeline->sync($timelineLogbook);

            if ($timelineLogbook->status === 'overdue_locked') {
                return redirect()->route('student.logbook.index')
                    ->with('error', 'This week is locked. Request an extension from your Academic Mentor.');
            }
            if ($timelineLogbook->status === 'scheduled') {
                return redirect()->route('student.logbook.index')
                    ->with('error', 'This week has not opened yet.');
            }
            if ($timelineLogbook->description) {
                return redirect()->route('student.logbook.show', $timelineLogbook);
            }
        }

        $totalWeeks = $this->totalWeeksFor($request->user());

        return view('student.logbook.create', compact('timelineLogbook', 'totalWeeks'));
    }

    public function store(Request $request, PlacementTimelineService $timeline)
    {
        $validated = $request->validate($this->rules(true, $this->totalWeeksFor($request->user())));
        $logbook = $request->user()->logbooks()
            ->where('week_number', $validated['week_number'])
            ->first();

        if ($logbook) {
            $timeline->sync($logbook);
            if ($logbook->status !== 'open' || $logbook->description) {
                return back()->with(
                    'error',
                    $logbook->status === 'overdue_locked'
                        ? 'This week is locked. Request an extension before submitting.'
                        : 'This weekly block is not available for a new submission.'
                );
            }
        }

        [$attendanceEntries, $renderedMinutes] = $this->prepareAttendance(
            $request,
            $validated
        );

        $evidencePath = $request->hasFile('evidence')
            ? $request->file('evidence')->store('logbook-evidence', 'public')
            : null;

        $data = [
            'week_number' => $validated['week_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $this->formatDescription($validated),
            'attendance_entries' => $attendanceEntries,
            'rendered_minutes' => $renderedMinutes,
            'status' => 'pending',
            'rejection_category' => null,
            'evidence_file_path' => $evidencePath,
        ];

        if ($logbook) {
            $logbook->update($data);
        } else {
            $logbook = Auth::user()->logbooks()->create($data);
        }

        return redirect()->route('student.logbook.show', $logbook)
            ->with('success', 'Week '.$validated['week_number'].' submitted for verification.');
    }

    public function show($id)
    {
        $logbook = Auth::user()->logbooks()->findOrFail($id);

        return view('student.logbook.show', compact('logbook'));
    }

    public function view(Logbook $logbook)
    {
        $this->authorizeDocumentAccess($logbook);
        $logbook->loadMissing(['student', 'approvedBy']);

        return view('logbooks.show', compact('logbook'));
    }

    public function edit($id)
    {
        $logbook = Auth::user()->logbooks()->findOrFail($id);
        abort_unless(in_array($logbook->status, ['pending', 'rejected'], true), 403);

        $parts = explode("=== Content & Skills ===\n", $logbook->description);
        $logbook->objectives = trim(str_replace("=== Type(s) & Objective(s) ===\n", '', $parts[0] ?? ''));
        $logbook->content = trim($parts[1] ?? '');

        return view('student.logbook.edit', compact('logbook'));
    }

    public function update(Request $request, $id)
    {
        $logbook = Auth::user()->logbooks()->findOrFail($id);
        abort_unless(in_array($logbook->status, ['pending', 'rejected'], true), 403);

        $validated = $request->validate($this->rules(false, $this->totalWeeksFor($request->user())));
        $oldAttendanceEntries = $logbook->attendance_entries ?? [];
        [$attendanceEntries, $renderedMinutes] = array_key_exists('attendance', $validated)
            ? $this->prepareAttendance($request, $validated, $oldAttendanceEntries)
            : [$oldAttendanceEntries, $logbook->rendered_minutes];

        $updateData = [
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $this->formatDescription($validated),
            'attendance_entries' => $attendanceEntries,
            'rendered_minutes' => $renderedMinutes,
            'verified_minutes' => null,
            'attendance_remarks' => null,
            'status' => 'pending',
            'supervisor_remarks' => null,
            'rejection_category' => null,
            'approved_by_id' => null,
            'approved_at' => null,
            'approval_signature_path' => null,
            'approval_stamp_path' => null,
            'approval_company_name' => null,
        ];
        $oldEvidencePath = null;

        if ($request->hasFile('evidence')) {
            $oldEvidencePath = $logbook->evidence_file_path;
            $updateData['evidence_file_path'] = $request->file('evidence')
                ->store('logbook-evidence', 'public');
        }

        $logbook->update($updateData);

        if ($oldEvidencePath) {
            Storage::disk('public')->delete($oldEvidencePath);
        }

        $oldMcPaths = collect($oldAttendanceEntries)->pluck('mc_evidence_path')->filter();
        $newMcPaths = collect($attendanceEntries)->pluck('mc_evidence_path')->filter();
        foreach ($oldMcPaths->diff($newMcPaths) as $oldMcPath) {
            Storage::disk('local')->delete($oldMcPath);
        }

        return redirect()->route('student.logbook.show', $logbook->id)
            ->with('success', 'Logbook updated and submitted for review.');
    }

    public function supervisorIndex(Request $request)
    {
        $studentIds = auth()->user()->supervisedStudents()->pluck('id');
        $pendingLogbooks = Logbook::with('student')
            ->whereIn('user_id', $studentIds)
            ->where('status', 'pending')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->whereHas('student', function ($student) use ($search): void {
                    $student->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('week'), fn ($query) => $query->where('week_number', (int) $request->input('week')))
            ->orderBy('created_at')
            ->paginate(25)
            ->withQueryString();
        $profile = auth()->user()->profile;
        $canSignLogbooks = $profile?->signature_path
            && $profile?->stamp_path
            && Storage::disk('local')->exists($profile->signature_path)
            && Storage::disk('local')->exists($profile->stamp_path);

        return view('supervisor.logbooks.index', compact(
            'pendingLogbooks',
            'canSignLogbooks'
        ));
    }

    public function approve(Request $request, $id)
    {
        $logbook = Logbook::findOrFail($id);
        $this->authorizeIndustrialSupervisor($logbook);
        $profile = $request->user()->profile;

        if (
            ! $profile?->signature_path
            || ! $profile?->stamp_path
            || ! Storage::disk('local')->exists($profile->signature_path)
            || ! Storage::disk('local')->exists($profile->stamp_path)
        ) {
            return back()->with(
                'error',
                'Upload your e-signature and company stamp in your profile before approving a logbook.'
            );
        }

        $validated = $request->validate([
            'verified_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'attendance_remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        $verifiedMinutes = $logbook->attendance_entries
            ? (int) round(((float) ($validated['verified_hours'] ?? $logbook->rendered_hours)) * 60)
            : null;

        if (
            $verifiedMinutes !== null
            && $verifiedMinutes < $logbook->rendered_minutes
            && empty($validated['attendance_remarks'])
        ) {
            throw ValidationException::withMessages([
                'attendance_remarks' => 'Explain why the verified hours were reduced.',
            ]);
        }

        $logbook->update([
            'status' => 'approved',
            'supervisor_remarks' => null,
            'rejection_category' => null,
            'verified_minutes' => $verifiedMinutes,
            'attendance_remarks' => $validated['attendance_remarks'] ?? null,
            'approved_by_id' => $request->user()->id,
            'approved_at' => now(),
            'approval_signature_path' => $profile->signature_path,
            'approval_stamp_path' => $profile->stamp_path,
            'approval_company_name' => $profile->company_name,
        ]);

        $this->notifySafely(
            $logbook->student,
            new WorkflowAlertNotification(
                'Week '.$logbook->week_number.' logbook approved',
                'Your Industrial Supervisor approved and signed your Week '.$logbook->week_number.' logbook.',
                route('student.logbook.index'),
                'success',
            )
        );

        return back()->with(
            'success',
            'Week '.$logbook->week_number.' attendance and logbook approved.'
        );
    }

    public function reject(Request $request, $id)
    {
        $logbook = Logbook::findOrFail($id);
        $this->authorizeIndustrialSupervisor($logbook);
        $validated = $request->validate([
            'issue_type' => ['required', Rule::in(['attendance', 'content'])],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'status' => 'rejected',
            'supervisor_remarks' => $validated['reason'],
            'rejection_category' => $validated['issue_type'],
            'verified_minutes' => null,
            'attendance_remarks' => $validated['issue_type'] === 'attendance'
                ? $validated['reason']
                : null,
            'approved_by_id' => null,
            'approved_at' => null,
            'approval_signature_path' => null,
            'approval_stamp_path' => null,
            'approval_company_name' => null,
        ]);

        $logbook->loadMissing('student.mentor');
        $this->notifySafely(
            $logbook->student,
            new WorkflowAlertNotification(
                'Week '.$logbook->week_number.' logbook needs revision',
                'Your Industrial Supervisor returned the submission: '.$validated['reason'],
                route('student.logbook.index'),
                'danger',
            )
        );

        if ($validated['issue_type'] === 'attendance') {
            $this->notifySafely(
                $logbook->student?->mentor,
                new WorkflowAlertNotification(
                    'Attendance alert: '.$logbook->student?->name,
                    'Week '.$logbook->week_number.' was rejected for an attendance issue: '.$validated['reason'],
                    route('mentor.dashboard'),
                    'danger',
                )
            );
        }

        return back()->with(
            'success',
            'Week '.$logbook->week_number.' was returned to the student.'
        );
    }

    public function requestExtension(
        Request $request,
        Logbook $logbook,
        PlacementTimelineService $timeline
    ) {
        abort_unless((int) $logbook->user_id === (int) $request->user()->id, 403);
        $timeline->sync($logbook);
        abort_unless($logbook->status === 'overdue_locked', 409);

        $validated = $request->validate([
            'extension_reason' => ['required', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'extension_status' => 'requested',
            'extension_reason' => $validated['extension_reason'],
            'extension_requested_at' => now(),
            'extension_until' => null,
            'extension_decision_note' => null,
            'extension_decided_at' => null,
        ]);

        $logbook->loadMissing('student.mentor');
        $this->notifySafely(
            $logbook->student?->mentor,
            new WorkflowAlertNotification(
                'Extension request: '.$logbook->student?->name,
                $logbook->student?->name.' requested an extension for Week '.$logbook->week_number.'.',
                route('mentor.dashboard'),
                'warning',
            )
        );

        return back()->with('success', 'Extension request sent to your Academic Mentor.');
    }

    public function approveExtension(Request $request, Logbook $logbook)
    {
        $this->authorizeAcademicMentor($logbook);
        abort_unless($logbook->extension_status === 'requested', 409);
        $validated = $request->validate([
            'extension_until' => ['required', 'date', 'after:now'],
            'extension_decision_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'status' => 'open',
            'locked_at' => null,
            'extension_status' => 'approved',
            'extension_until' => $validated['extension_until'],
            'extension_decision_note' => $validated['extension_decision_note'] ?? null,
            'extension_decided_at' => now(),
        ]);

        $this->notifySafely(
            $logbook->student,
            new WorkflowAlertNotification(
                'Week '.$logbook->week_number.' extension approved',
                'Your Academic Mentor approved the extension until '.$logbook->extension_until->format('d M Y, H:i').'.',
                route('student.logbook.index'),
                'success',
            )
        );

        return back()->with('success', 'Extension approved and weekly block unlocked.');
    }

    public function rejectExtension(Request $request, Logbook $logbook)
    {
        $this->authorizeAcademicMentor($logbook);
        abort_unless($logbook->extension_status === 'requested', 409);
        $validated = $request->validate([
            'extension_decision_note' => ['required', 'string', 'max:2000'],
        ]);

        $logbook->update([
            'extension_status' => 'rejected',
            'extension_decision_note' => $validated['extension_decision_note'],
            'extension_decided_at' => now(),
        ]);

        $this->notifySafely(
            $logbook->student,
            new WorkflowAlertNotification(
                'Week '.$logbook->week_number.' extension rejected',
                'Your extension request was rejected: '.$validated['extension_decision_note'],
                route('student.logbook.index'),
                'danger',
            )
        );

        return back()->with('success', 'Extension request rejected.');
    }

    public function supervisorHistory(Request $request)
    {
        $studentIds = auth()->user()->supervisedStudents()->pluck('id');
        $logbooks = Logbook::with('student')
            ->whereIn('user_id', $studentIds)
            ->whereIn('status', ['approved', 'rejected'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->whereHas('student', function ($student) use ($search): void {
                    $student->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($request->input('status'), ['approved', 'rejected'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('week'), fn ($query) => $query->where('week_number', (int) $request->input('week')))
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('supervisor.logbooks.history', compact('logbooks'));
    }

    public function downloadEvidence(Logbook $logbook)
    {
        $this->authorizeDocumentAccess($logbook);
        abort_unless(
            $logbook->evidence_file_path
                && Storage::disk('public')->exists($logbook->evidence_file_path),
            404
        );

        return Storage::disk('public')->download($logbook->evidence_file_path);
    }

    private function notifySafely($recipient, WorkflowAlertNotification $notification): void
    {
        if (! $recipient) {
            return;
        }

        try {
            $recipient->notify($notification);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function downloadMcEvidence(Logbook $logbook, int $day)
    {
        $this->authorizeDocumentAccess($logbook);
        $entry = $logbook->attendance_entries[$day] ?? null;
        $path = $entry['mc_evidence_path'] ?? null;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'week-'.$logbook->week_number.'-'.strtolower(Carbon::parse($entry['date'])->format('l')).'-mc.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    public function viewApprovalAsset(Logbook $logbook, string $asset)
    {
        $this->authorizeDocumentAccess($logbook);
        abort_unless($logbook->status === 'approved', 404);

        $path = match ($asset) {
            'signature' => $logbook->approval_signature_path,
            'stamp' => $logbook->approval_stamp_path,
            default => null,
        };

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, basename($path), [], 'inline');
    }

    private function rules(bool $creating, int $totalWeeks = 16): array
    {
        $rules = [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'objectives' => ['required', 'string'],
            'content' => ['required', 'string'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
            'attendance' => [$creating ? 'required' : 'sometimes', 'array', 'size:5'],
            'attendance.*.date' => ['required_with:attendance', 'date', 'distinct'],
            'attendance.*.status' => [
                'required_with:attendance',
                Rule::in(['present', 'approved_leave', 'medical_leave', 'public_holiday']),
            ],
            'attendance.*.rendered_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'attendance.*.note' => ['nullable', 'string', 'max:500'],
            'attendance.*.mc_evidence' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];

        if ($creating) {
            $rules['week_number'] = [
                'required',
                'integer',
                'min:1',
                'max:'.$totalWeeks,
            ];
        }

        return $rules;
    }

    private function totalWeeksFor($user): int
    {
        $placement = $user->latestPlacementClearance;

        if ($placement?->cycle?->duration_weeks) {
            return (int) $placement->cycle->duration_weeks;
        }

        $generatedMax = (int) $user->logbooks()
            ->where('timeline_generated', true)
            ->max('week_number');

        return max(16, $generatedMax);
    }

    private function prepareAttendance(
        Request $request,
        array $validated,
        array $existingEntries = []
    ): array {
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();
        $errors = [];

        if (! $startDate->isMonday()) {
            $errors['start_date'] = 'The weekly logbook must start on Monday.';
        }
        if (! $endDate->isFriday() || $startDate->copy()->addDays(4)->toDateString() !== $endDate->toDateString()) {
            $errors['end_date'] = 'The weekly logbook must end on Friday of the same week.';
        }

        foreach ($validated['attendance'] as $index => $entry) {
            $date = Carbon::parse($entry['date']);
            $status = $entry['status'];
            $expectedDate = $startDate->copy()->addDays($index)->toDateString();

            if ($date->toDateString() !== $expectedDate) {
                $errors["attendance.$index.date"] = 'The attendance date must match the selected Monday-Friday week.';
            }

            if ($status === 'present') {
                if ((float) ($entry['rendered_hours'] ?? 0) <= 0) {
                    $errors["attendance.$index.rendered_hours"] = 'Enter the rendered hours for a present day.';
                }
            }

            if (
                $status === 'medical_leave'
                && ! $request->hasFile("attendance.$index.mc_evidence")
                && empty($existingEntries[$index]['mc_evidence_path'])
            ) {
                $errors["attendance.$index.mc_evidence"] = 'Upload the MC for this specific day.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $entries = [];
        $renderedMinutes = 0;
        foreach ($validated['attendance'] as $index => $entry) {
            $status = $entry['status'];
            $minutes = $status === 'present'
                ? (int) round(((float) $entry['rendered_hours']) * 60)
                : 0;
            $mcEvidencePath = null;

            if ($status === 'medical_leave') {
                $mcEvidencePath = $request->hasFile("attendance.$index.mc_evidence")
                    ? $request->file("attendance.$index.mc_evidence")
                        ->store('logbook-mc/'.Auth::id(), 'local')
                    : ($existingEntries[$index]['mc_evidence_path'] ?? null);
            }

            $renderedMinutes += $minutes;
            $entries[] = [
                'date' => Carbon::parse($entry['date'])->format('Y-m-d'),
                'status' => $status,
                'rendered_minutes' => $minutes,
                'note' => $status === 'present' ? null : trim($entry['note'] ?? ''),
                'mc_evidence_path' => $mcEvidencePath,
            ];
        }

        return [$entries, $renderedMinutes];
    }

    private function formatDescription(array $validated): string
    {
        return "=== Type(s) & Objective(s) ===\n"
            .$validated['objectives']
            ."\n\n=== Content & Skills ===\n"
            .$validated['content'];
    }

    private function authorizeIndustrialSupervisor(Logbook $logbook): void
    {
        abort_unless(
            (int) $logbook->student?->supervisor_id === (int) auth()->id(),
            403
        );
    }

    private function authorizeDocumentAccess(Logbook $logbook): void
    {
        $user = Auth::user();
        $isOwner = $user->isStudent() && (int) $logbook->user_id === (int) $user->id;
        $isIndustrialSupervisor = $user->isSupervisor()
            && (int) $logbook->student?->supervisor_id === (int) $user->id;
        $isAcademicMentor = $user->isMentor()
            && (int) $logbook->student?->mentor_id === (int) $user->id;

        abort_unless($isOwner || $isIndustrialSupervisor || $isAcademicMentor || $user->isAdmin(), 403);
    }

    private function authorizeAcademicMentor(Logbook $logbook): void
    {
        abort_unless(
            (int) $logbook->student?->mentor_id === (int) auth()->id(),
            403
        );
    }
}
