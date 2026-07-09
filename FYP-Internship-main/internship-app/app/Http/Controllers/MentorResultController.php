<?php

namespace App\Http\Controllers;

use App\Models\InternshipCycle;
use App\Models\InternshipResult;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentorResultController extends Controller
{
    public function store(Request $request, User $student)
    {
        $this->authorizeStudent($request, $student);
        abort_if($student->internshipResult()->exists(), 409);

        $finalEvaluation = PerformanceEvaluation::where('student_id', $student->id)
            ->where('type', PerformanceEvaluation::TYPE_FINAL)
            ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
            ->first();
        $logbooks = $student->logbooks()->where('timeline_generated', true)->get();
        $totalWeeks = $finalEvaluation?->cycle?->duration_weeks
            ?? $student->latestPlacementClearance?->cycle?->duration_weeks
            ?? 16;
        $resolvedCount = $logbooks->whereIn('status', [
            'approved',
            'rejected',
            'overdue_locked',
        ])->count();

        if (! $finalEvaluation || $logbooks->count() !== $totalWeeks || $resolvedCount !== $totalWeeks) {
            return back()->with(
                'error',
                "Finalization requires the final Industrial Supervisor evaluation and all {$totalWeeks} resolved weekly blocks."
            );
        }

        $validated = $request->validate([
            'result' => ['required', Rule::in(['pass', 'fail'])],
            'rationale' => ['required', 'string', 'max:5000'],
        ]);

        $approvedCount = $logbooks->where('status', 'approved')->count();
        $evidenceSummary = "Evidence summary: approved logbooks {$approvedCount}/{$totalWeeks}; final supervisor score {$finalEvaluation->overall_grade}/10.";

        InternshipResult::create([
            'student_id' => $student->id,
            'internship_cycle_id' => $student->latestPlacementClearance?->internship_cycle_id,
            'mentor_id' => $request->user()->id,
            'final_evaluation_id' => $finalEvaluation->id,
            'approved_logbooks' => $approvedCount,
            'total_logbooks' => $totalWeeks,
            'supervisor_score' => $finalEvaluation->overall_grade,
            'result' => $validated['result'],
            'rationale' => trim($validated['rationale'])."\n\n".$evidenceSummary,
            'locked_at' => now(),
        ]);

        return back()->with('success', 'Final module result selected by Academic Mentor and locked.');
    }

    public function export(Request $request)
    {
        $activeCycle = InternshipCycle::active();
        $students = $request->user()->assignedStudents()
            ->when($activeCycle, fn ($query) => $query->whereHas(
                'cycleAssignments',
                fn ($assignment) => $assignment
                    ->where('internship_cycle_id', $activeCycle->id)
                    ->where('mentor_id', $request->user()->id)
            ))
            ->with([
                'internshipResult' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
                'logbooks' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
                'performanceEvaluations' => fn ($query) => $query
                    ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id)),
            ])
            ->orderBy('name')
            ->get();
        $placements = PlacementClearance::whereIn('student_id', $students->pluck('id'))
            ->whereIn('status', ['approved', 'completed'])
            ->when($activeCycle, fn ($query) => $query->where('internship_cycle_id', $activeCycle->id))
            ->latest('approved_at')
            ->get()
            ->keyBy('student_id');

        return response()->streamDownload(function () use ($students, $placements) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Student',
                'Email',
                'Company',
                'Placement Start',
                'Placement End',
                'Approved Logbooks',
                'Final Supervisor Score',
                'Result',
                'Result Locked At',
            ]);

            foreach ($students as $student) {
                $placement = $placements->get($student->id);
                $finalEvaluation = $student->performanceEvaluations
                    ->where('type', PerformanceEvaluation::TYPE_FINAL)
                    ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
                    ->first();
                fputcsv($output, [
                    $student->name,
                    $student->email,
                    $placement?->company_name,
                    $placement?->start_date?->format('Y-m-d'),
                    $placement?->end_date?->format('Y-m-d'),
                    $student->logbooks->where('status', 'approved')->count(),
                    $finalEvaluation?->overall_grade,
                    strtoupper($student->internshipResult?->result ?? 'PENDING'),
                    $student->internshipResult?->locked_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, 'academic-mentor-cohort-results.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function authorizeStudent(Request $request, User $student): void
    {
        abort_unless(
            $student->isStudent()
                && (int) $student->mentor_id === (int) $request->user()->id,
            403
        );
    }
}
