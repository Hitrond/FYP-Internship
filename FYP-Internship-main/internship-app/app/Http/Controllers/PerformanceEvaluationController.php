<?php

namespace App\Http\Controllers;

use App\Models\EvaluationForm;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PerformanceEvaluationController extends Controller
{
    public function supervisorIndex(Request $request)
    {
        $students = $request->user()->supervisedStudents()
            ->with([
                'performanceEvaluations' => fn ($query) => $query
                    ->where('supervisor_id', $request->user()->id),
                'profile',
            ])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($profile) => $profile->where('tp_number', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->get();
        if (in_array($request->input('type'), [PerformanceEvaluation::TYPE_MIDTERM, PerformanceEvaluation::TYPE_FINAL], true)
            && in_array($request->input('status'), ['not_started', PerformanceEvaluation::STATUS_DRAFT, PerformanceEvaluation::STATUS_SUBMITTED], true)
        ) {
            $type = $request->input('type');
            $status = $request->input('status');
            $students = $students
                ->filter(function ($student) use ($type, $status): bool {
                    $evaluation = $student->performanceEvaluations->firstWhere('type', $type);

                    return $status === 'not_started'
                        ? ! $evaluation
                        : $evaluation?->status === $status;
                })
                ->values();
        }

        return view('supervisor.evaluations.index', compact('students'));
    }

    public function edit(Request $request, User $student, string $type)
    {
        $this->authorizeAssignedSupervisor($request, $student);
        $this->validateType($type);
        $evaluation = PerformanceEvaluation::where('student_id', $student->id)
            ->where('type', $type)
            ->first();
        $cycleId = $student->latestPlacementClearance?->internship_cycle_id;
        $formTemplate = $evaluation?->form ?: EvaluationForm::activeFor($type, $cycleId);
        $criteria = $formTemplate?->criteria ?: PerformanceEvaluation::DEFAULT_CRITERIA;

        return view('supervisor.evaluations.edit', [
            'student' => $student,
            'type' => $type,
            'evaluation' => $evaluation,
            'criteria' => $criteria,
            'formTemplate' => $formTemplate,
        ]);
    }

    public function store(Request $request, User $student, string $type)
    {
        $this->authorizeAssignedSupervisor($request, $student);
        $this->validateType($type);
        $evaluation = PerformanceEvaluation::where('student_id', $student->id)
            ->where('type', $type)
            ->first();
        $cycleId = $student->latestPlacementClearance?->internship_cycle_id;
        $formTemplate = $evaluation?->form ?: EvaluationForm::activeFor($type, $cycleId);
        $criteria = $formTemplate?->criteria ?: PerformanceEvaluation::DEFAULT_CRITERIA;

        abort_if($evaluation?->status === PerformanceEvaluation::STATUS_SUBMITTED, 409);

        $rules = [
            'action' => ['required', Rule::in(['draft', 'submit'])],
            'overall_grade' => ['required', 'integer', 'min:1', 'max:10'],
            'overall_comments' => [
                Rule::requiredIf($request->input('action') === 'submit'),
                'nullable',
                'string',
                'max:5000',
            ],
        ];

        foreach ($criteria as $key => $label) {
            $rules["ratings.$key.rating"] = [
                'required',
                Rule::in(['A', 'B', 'C', 'D', 'U']),
            ];
            $rules["ratings.$key.comment"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);
        foreach ($criteria as $key => $label) {
            if (
                $validated['ratings'][$key]['rating'] === 'D'
                && empty(trim($validated['ratings'][$key]['comment'] ?? ''))
            ) {
                throw ValidationException::withMessages([
                    "ratings.$key.comment" => 'A comment is required for a Poor rating.',
                ]);
            }
        }

        $isSubmit = $validated['action'] === 'submit';
        PerformanceEvaluation::updateOrCreate(
            ['student_id' => $student->id, 'type' => $type],
            [
                'internship_cycle_id' => $cycleId,
                'evaluation_form_id' => $formTemplate?->id,
                'supervisor_id' => $request->user()->id,
                'ratings' => $validated['ratings'],
                'overall_grade' => $validated['overall_grade'],
                'overall_comments' => $validated['overall_comments'] ?? null,
                'status' => $isSubmit
                    ? PerformanceEvaluation::STATUS_SUBMITTED
                    : PerformanceEvaluation::STATUS_DRAFT,
                'submitted_at' => $isSubmit ? now() : null,
            ]
        );

        return redirect()->route('supervisor.evaluations.index')
            ->with(
                'success',
                ucfirst($type).' evaluation '.($isSubmit ? 'submitted.' : 'saved as draft.')
            );
    }

    public function studentIndex(Request $request)
    {
        $evaluations = PerformanceEvaluation::with(['supervisor', 'form'])
            ->where('student_id', $request->user()->id)
            ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
            ->orderBy('submitted_at')
            ->get();

        return view('evaluations.index', [
            'evaluations' => $evaluations,
            'title' => 'My Performance Evaluations',
            'description' => 'Feedback submitted by your Industrial Supervisor.',
            'showStudent' => false,
        ]);
    }

    public function mentorIndex(Request $request)
    {
        $studentIds = $request->user()->assignedStudents()->pluck('id');
        $evaluations = PerformanceEvaluation::with(['student', 'supervisor', 'form', 'cycle'])
            ->whereIn('student_id', $studentIds)
            ->where('status', PerformanceEvaluation::STATUS_SUBMITTED)
            ->when($request->filled('semester'), fn ($query) => $query->where('internship_cycle_id', $request->integer('semester')))
            ->latest('submitted_at')
            ->get();

        return view('evaluations.index', [
            'evaluations' => $evaluations,
            'title' => 'Student Performance Monitoring',
            'description' => 'Submitted workplace evaluations for your assigned students. Use the semester filter when you supervise multiple intakes.',
            'showStudent' => true,
            'cycles' => \App\Models\InternshipCycle::latest('placement_window_start')->get(),
        ]);
    }

    private function authorizeAssignedSupervisor(Request $request, User $student): void
    {
        abort_unless(
            $student->isStudent()
                && (int) $student->supervisor_id === (int) $request->user()->id,
            403
        );
    }

    private function validateType(string $type): void
    {
        abort_unless(in_array($type, [
            PerformanceEvaluation::TYPE_MIDTERM,
            PerformanceEvaluation::TYPE_FINAL,
        ], true), 404);
    }
}
