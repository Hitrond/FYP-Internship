<?php

namespace App\Http\Controllers;

use App\Models\InternshipCycle;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Services\PlacementTimelineService;
use Illuminate\Http\Request;

class MentorDashboardController extends Controller
{
    public function index(Request $request, PlacementTimelineService $timeline)
    {
        $mentor = $request->user();
        $cycles = InternshipCycle::whereHas(
            'assignments',
            fn ($query) => $query->where('mentor_id', $mentor->id)
        )
            ->latest('placement_window_start')
            ->get();
        $selectedCycle = $request->filled('semester')
            ? $cycles->firstWhere('id', $request->integer('semester'))
            : InternshipCycle::active();
        $activeCycle = $selectedCycle;

        $pendingClearances = PlacementClearance::with('student')
            ->whereHas('student', fn ($query) => $query->where('mentor_id', $mentor->id))
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = PlacementClearance::whereHas(
            'student',
            fn ($query) => $query->where('mentor_id', $mentor->id)
        )
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->where('status', 'pending')
            ->count();

        $assignedStudents = $mentor->assignedStudents()
            ->when($activeCycle, fn ($query) => $query->whereHas(
                'cycleAssignments',
                fn ($assignment) => $assignment
                    ->where('internship_cycle_id', $activeCycle->id)
                    ->where('mentor_id', $mentor->id)
            ))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->with([
                'profile',
                'applications',
                'latestPlacementClearance' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
                'logbooks' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
                'performanceEvaluations' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
                'internshipResult' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
            ])
            ->orderBy('name')
            ->get();
        $studentStatus = $request->input('student_status');
        if ($studentStatus) {
            $assignedStudents = $assignedStudents
                ->filter(function ($student) use ($studentStatus, $activeCycle): bool {
                    $applicationCount = $student->applications->count();
                    $rejectedCount = $student->applications->where('status', 'Rejected')->count();
                    $highRejection = $applicationCount >= 3 && ($rejectedCount / max(1, $applicationCount)) >= 0.5;
                    $expectedWeeks = $activeCycle?->duration_weeks
                        ?? max(16, (int) $student->logbooks->where('timeline_generated', true)->max('week_number'));
                    $resolvedWeeks = $student->logbooks
                        ->whereIn('status', ['approved', 'rejected', 'overdue_locked'])
                        ->count();
                    $finalEvaluation = $student->performanceEvaluations
                        ->where('type', 'final')
                        ->where('status', 'submitted')
                        ->first();

                    return match ($studentStatus) {
                        'no_applications' => $applicationCount === 0,
                        'high_rejection' => $highRejection,
                        'pending_logbooks' => $student->logbooks->where('status', 'pending')->isNotEmpty(),
                        'can_finalize' => ! $student->internshipResult
                            && $student->logbooks->where('timeline_generated', true)->count() === $expectedWeeks
                            && $resolvedWeeks === $expectedWeeks
                            && (bool) $finalEvaluation,
                        'result_pending' => ! $student->internshipResult,
                        'pass' => $student->internshipResult?->result === 'pass',
                        'fail' => $student->internshipResult?->result === 'fail',
                        default => true,
                    };
                })
                ->values();
        }
        $assignedStudents->each(
            fn ($student) => $student->logbooks->each(
                fn (Logbook $logbook) => $timeline->sync($logbook)
            )
        );

        $attendanceAlerts = Logbook::with('student')
            ->whereHas('student', fn ($query) => $query->where('mentor_id', $mentor->id))
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->where('status', 'rejected')
            ->where('rejection_category', 'attendance')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $evaluationAlerts = PerformanceEvaluation::with(['student', 'supervisor'])
            ->whereIn('student_id', $assignedStudents->pluck('id'))
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
            ->latest('submitted_at')
            ->get()
            ->filter(fn (PerformanceEvaluation $evaluation) => $evaluation->hasConcern());
        $recentLogbooks = Logbook::with('student')
            ->whereIn('user_id', $assignedStudents->pluck('id'))
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->latest('updated_at')
            ->take(8)
            ->get();
        $pendingExtensions = Logbook::with('student')
            ->whereIn('user_id', $assignedStudents->pluck('id'))
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->where('extension_status', 'requested')
            ->oldest('extension_requested_at')
            ->get();

        $workflowStage = 'pre_placement';
        if ($pendingCount > 0) {
            $workflowStage = 'placement_review';
        } elseif ($assignedStudents->contains(fn ($student) => $student->latestPlacementClearance?->status === 'approved')) {
            $workflowStage = 'internship';
        }
        if ($assignedStudents->isNotEmpty() && $assignedStudents->every(fn ($student) => (bool) $student->internshipResult)) {
            $workflowStage = 'completion';
        }

        return view('mentor.dashboard', compact(
            'pendingClearances',
            'pendingCount',
            'assignedStudents',
            'attendanceAlerts',
            'evaluationAlerts',
            'recentLogbooks',
            'pendingExtensions',
            'activeCycle',
            'cycles',
            'selectedCycle',
            'workflowStage',
        ));
    }
}
