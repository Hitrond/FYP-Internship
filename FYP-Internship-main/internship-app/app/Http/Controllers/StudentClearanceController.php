<?php

namespace App\Http\Controllers;

use App\Models\InternshipCycle;
use App\Models\PlacementClearance;
use App\Services\PlacementTimelineService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class StudentClearanceController extends Controller
{
    public function create(Request $request)
    {
        $student = $request->user();
        $activeCycle = InternshipCycle::active();
        $cyclesConfigured = InternshipCycle::exists();
        $cycleAssignment = $activeCycle
            ? $student->cycleAssignments()->where('internship_cycle_id', $activeCycle->id)->first()
            : null;
        $latestClearance = PlacementClearance::where('student_id', $student->id)
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->latest()
            ->first();

        $prefillClearance = $latestClearance;

        return view('student.clearance.create', compact(
            'latestClearance',
            'prefillClearance',
            'activeCycle',
            'cycleAssignment',
            'cyclesConfigured',
        ));
    }

    public function store(Request $request)
    {
        // ADDED THIS LINE: Define the student before we try to use it!
        $student = $request->user();
        $activeCycle = InternshipCycle::active();

        if (! $activeCycle && InternshipCycle::exists()) {
            return back()->with('error', 'Placement submissions are closed because there is no active internship semester.');
        }

        if ($activeCycle) {
            $assignment = $student->cycleAssignments()
                ->where('internship_cycle_id', $activeCycle->id)
                ->first();

            if (! $assignment) {
                return back()->with('error', 'You are not enrolled in the active internship semester.');
            }
            if (! $assignment->mentor_id) {
                return back()->with('error', 'An Academic Mentor must be assigned before placement submission.');
            }

            if ((int) $student->mentor_id !== (int) $assignment->mentor_id) {
                $student->update(['mentor_id' => $assignment->mentor_id]);
                $student->refresh();
            }
        }

        $latestClearance = PlacementClearance::where('student_id', $student->id)
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->latest()
            ->first();

        if ($latestClearance && $latestClearance->status === 'pending') {
            return redirect()->route('student.clearance.create')
                ->with('error', 'You already have a pending submission. Please wait for mentor review.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'office_address' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'supervisor_name' => ['required', 'string', 'max:255'],
            'supervisor_email' => ['required', 'string', 'email', 'max:255'],
            'supervisor_personal_email' => ['required', 'string', 'email', 'max:255'],
            'job_offer' => ['required', 'file', 'extensions:pdf', 'max:102400'],
            'indemnity_letter' => ['required', 'file', 'extensions:pdf', 'max:102400'],
            'placement_agreement' => ['required', 'file', 'extensions:pdf', 'max:102400'],
        ], [
            'job_offer.max' => 'The job offer is too large. Upload a PDF no larger than 100 MB.',
            'indemnity_letter.max' => 'The indemnity letter is too large. Upload a PDF no larger than 100 MB.',
            'placement_agreement.max' => 'The placement agreement is too large. Upload a PDF no larger than 100 MB.',
        ]);

        $this->validatePlacementDates($validated['start_date'], $validated['end_date'], $activeCycle);

        $jobOfferPath = $request->file('job_offer')->store('clearances', 'local');
        $indemnityPath = $request->file('indemnity_letter')->store('clearances', 'local');
        $agreementPath = $request->file('placement_agreement')->store('clearances', 'local');

        $clearance = PlacementClearance::create([
            'student_id' => $student->id,
            'internship_cycle_id' => $activeCycle?->id,
            'mentor_id' => $student->mentor_id,
            'company_name' => $validated['company_name'],
            'office_address' => $validated['office_address'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'supervisor_name' => $validated['supervisor_name'],
            'supervisor_email' => $validated['supervisor_email'],
            'supervisor_personal_email' => $validated['supervisor_personal_email'],
            'job_offer_path' => $jobOfferPath,
            'indemnity_path' => $indemnityPath,
            'placement_agreement_path' => $agreementPath,
            'status' => 'pending',
        ]);

        $warning = null;
        $mentorEmail = $student->mentor?->email;

        if ($mentorEmail) {
            try {
                Mail::raw(
                    "A new placement clearance submission is ready for review.\n\n".
                    "Student ID: {$clearance->student_id}\n".
                    "Company: {$clearance->company_name}\n".
                    "Supervisor: {$clearance->supervisor_name}",
                    function ($message) use ($mentorEmail) {
                        $message->to($mentorEmail)
                            ->subject('New Placement Clearance Submission');
                    }
                );
            } catch (\Throwable $exception) {
                $warning = 'Submission saved, but mentor email notification failed.';
            }
        } else {
            $warning = 'Submission saved, but no mentor is assigned to this student.';
        }

        return redirect()->route('student.clearance.create')
            ->with($warning ? 'warning' : 'success', $warning ?? 'Placement submission sent to mentor for review.');
    }

    public function updateDates(
        Request $request,
        PlacementClearance $placementClearance,
        PlacementTimelineService $timeline
    ) {
        abort_unless(
            (int) $placementClearance->student_id === (int) $request->user()->id,
            403
        );
        abort_if(
            $request->user()->logbooks()->where('timeline_generated', true)->exists(),
            409
        );
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);
        $this->validatePlacementDates(
            $validated['start_date'],
            $validated['end_date'],
            $placementClearance->cycle
        );

        $placementClearance->update($validated);
        if (in_array($placementClearance->status, ['approved', 'completed'], true)) {
            $timeline->generate($placementClearance->fresh());
        }

        return back()->with('success', 'Official dates saved and the internship timeline generated.');
    }

    private function validatePlacementDates(
        string $startDate,
        string $endDate,
        ?InternshipCycle $cycle = null
    ): void {
        $placementStart = Carbon::parse($startDate);
        $durationWeeks = $cycle?->duration_weeks ?? 16;
        $expectedEnd = $this->expectedEndDate($placementStart, $durationWeeks);
        $errors = [];

        if (
            $cycle
            && (
                $placementStart->lt($cycle->placement_window_start)
                || $placementStart->gt($cycle->placement_window_end)
            )
        ) {
            $errors['start_date'] = 'The start date must fall within the active semester placement window.';
        }
        if (Carbon::parse($endDate)->toDateString() !== $expectedEnd->toDateString()) {
            $errors['end_date'] = "For a {$durationWeeks}-week placement, the end date must be ".$expectedEnd->format('M d, Y').'.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function expectedEndDate(Carbon $placementStart, int $durationWeeks): Carbon
    {
        $weekStart = $placementStart->copy()->startOfDay();
        $weekEnd = $weekStart->copy();

        for ($week = 1; $week <= $durationWeeks; $week++) {
            $weekEnd = $weekStart->copy();

            while (! $weekEnd->isFriday()) {
                $weekEnd->addDay();
            }

            $weekStart = $weekEnd->copy()->next(Carbon::MONDAY)->startOfDay();
        }

        return $weekEnd;
    }
}
