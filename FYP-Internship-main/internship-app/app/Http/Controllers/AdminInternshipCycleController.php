<?php

namespace App\Http\Controllers;

use App\Models\InternshipCycle;
use App\Models\InternshipCycleStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminInternshipCycleController extends Controller
{
    public function index()
    {
        $cycles = InternshipCycle::withCount([
            'assignments',
            'placements',
            'placements as approved_placements_count' => fn ($query) => $query->whereIn('status', ['approved', 'completed']),
            'logbooks as approved_logbooks_count' => fn ($query) => $query->where('status', 'approved'),
        ])
            ->latest('placement_window_start')
            ->get();

        return view('admin.semesters.index', compact('cycles'));
    }

    public function create()
    {
        return view('admin.semesters.create', ['semester' => new InternshipCycle]);
    }

    public function store(Request $request)
    {
        $cycle = InternshipCycle::create($this->validatedCycle($request) + [
            'status' => InternshipCycle::STATUS_DRAFT,
        ]);

        return redirect()->route('admin.semesters.show', $cycle)
            ->with('success', 'Semester created as a draft. Add students before activating it.');
    }

    public function show(InternshipCycle $semester)
    {
        $semester->loadCount([
            'assignments',
            'placements',
            'placements as approved_placements_count' => fn ($query) => $query->whereIn('status', ['approved', 'completed']),
            'logbooks as approved_logbooks_count' => fn ($query) => $query->where('status', 'approved'),
            'logbooks as overdue_logbooks_count' => fn ($query) => $query->where('status', 'overdue_locked'),
        ]);

        $assignments = $semester->assignments()
            ->with(['student.profile', 'student.supervisor', 'mentor'])
            ->get()
            ->sortBy(fn ($assignment) => $assignment->student?->name)
            ->values();

        $assignedStudentIds = $assignments->pluck('student_id');
        $activeSemesterStudentIds = InternshipCycleStudent::query()
            ->where('internship_cycle_id', '!=', $semester->id)
            ->whereHas(
                'cycle',
                fn ($query) => $query->where('status', InternshipCycle::STATUS_ACTIVE)
            )
            ->pluck('student_id');
        $availableStudents = User::where('role', 'student')
            ->whereNotIn('id', $assignedStudentIds)
            ->whereNotIn('id', $activeSemesterStudentIds)
            ->with('profile')
            ->orderBy('name')
            ->get();
        $mentors = User::where('role', 'mentor')->orderBy('name')->get();

        return view('admin.semesters.show', compact(
            'semester',
            'assignments',
            'availableStudents',
            'activeSemesterStudentIds',
            'mentors',
        ));
    }

    public function edit(InternshipCycle $semester)
    {
        abort_unless(in_array($semester->status, [
            InternshipCycle::STATUS_DRAFT,
            InternshipCycle::STATUS_ACTIVE,
        ], true), 409);

        return view('admin.semesters.create', compact('semester'));
    }

    public function update(Request $request, InternshipCycle $semester)
    {
        abort_unless(in_array($semester->status, [
            InternshipCycle::STATUS_DRAFT,
            InternshipCycle::STATUS_ACTIVE,
        ], true), 409);
        $semester->update($this->validatedCycle($request, $semester));

        return redirect()->route('admin.semesters.show', $semester)
            ->with('success', 'Semester settings updated.');
    }

    public function assignStudents(Request $request, InternshipCycle $semester)
    {
        abort_if(in_array($semester->status, [
            InternshipCycle::STATUS_CLOSED,
            InternshipCycle::STATUS_ARCHIVED,
        ], true), 409);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'student')),
            ],
            'mentor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'mentor')),
            ],
        ]);

        $activeAssignments = InternshipCycleStudent::query()
            ->with(['student', 'cycle'])
            ->where('internship_cycle_id', '!=', $semester->id)
            ->whereIn('student_id', array_unique($validated['student_ids']))
            ->whereHas(
                'cycle',
                fn ($query) => $query->where('status', InternshipCycle::STATUS_ACTIVE)
            )
            ->get();

        if ($activeAssignments->isNotEmpty()) {
            $studentNames = $activeAssignments
                ->map(fn (InternshipCycleStudent $assignment) => $assignment->student?->name)
                ->filter()
                ->unique()
                ->implode(', ');

            return back()
                ->withInput()
                ->withErrors([
                    'student_ids' => ($studentNames ?: 'One or more selected students')
                        .' cannot be added because they are enrolled in an active semester. Remove them from the active semester first.',
                ]);
        }

        DB::transaction(function () use ($semester, $validated): void {
            foreach (array_unique($validated['student_ids']) as $studentId) {
                InternshipCycleStudent::updateOrCreate(
                    [
                        'internship_cycle_id' => $semester->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'mentor_id' => $validated['mentor_id'] ?? null,
                        'status' => 'enrolled',
                        'assigned_at' => now(),
                    ]
                );

                if ($semester->status === InternshipCycle::STATUS_ACTIVE) {
                    User::whereKey($studentId)->update([
                        'mentor_id' => $validated['mentor_id'] ?? null,
                    ]);
                }
            }
        });

        return back()->with('success', count($validated['student_ids']).' student(s) added to the cohort.');
    }

    public function updateAssignment(
        Request $request,
        InternshipCycle $semester,
        User $student
    ) {
        abort_unless($student->isStudent(), 422);
        abort_if(in_array($semester->status, [
            InternshipCycle::STATUS_CLOSED,
            InternshipCycle::STATUS_ARCHIVED,
        ], true), 409);

        $validated = $request->validate([
            'mentor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'mentor')),
            ],
        ]);

        $assignment = $semester->assignments()->where('student_id', $student->id)->firstOrFail();
        $assignment->update(['mentor_id' => $validated['mentor_id'] ?? null]);

        if ($semester->status === InternshipCycle::STATUS_ACTIVE) {
            $student->update(['mentor_id' => $validated['mentor_id'] ?? null]);
        }

        return back()->with('success', 'Academic Mentor assignment updated for '.$student->name.'.');
    }

    public function removeStudent(InternshipCycle $semester, User $student)
    {
        abort_unless($student->isStudent(), 422);
        abort_if(in_array($semester->status, [
            InternshipCycle::STATUS_CLOSED,
            InternshipCycle::STATUS_ARCHIVED,
        ], true), 409);
        abort_if(
            $semester->placements()->where('student_id', $student->id)->exists(),
            409,
            'A student with a placement submission cannot be removed from the semester.'
        );

        $semester->assignments()->where('student_id', $student->id)->delete();

        if ($semester->status === InternshipCycle::STATUS_ACTIVE) {
            $student->update(['mentor_id' => null]);
        }

        return back()->with('success', $student->name.' was removed from the cohort.');
    }

    public function activate(InternshipCycle $semester)
    {
        abort_unless($semester->status === InternshipCycle::STATUS_DRAFT, 409);

        if (InternshipCycle::where('status', InternshipCycle::STATUS_ACTIVE)
            ->where('id', '!=', $semester->id)
            ->exists()) {
            return back()->with('error', 'Close the currently active semester before activating another.');
        }

        if (! $semester->assignments()->exists()) {
            return back()->with('error', 'Add at least one student before activating this semester.');
        }

        DB::transaction(function () use ($semester): void {
            $semester->update([
                'status' => InternshipCycle::STATUS_ACTIVE,
                'activated_at' => now(),
                'closed_at' => null,
            ]);

            $semester->assignments()->each(function (InternshipCycleStudent $assignment): void {
                $assignment->student()->update(['mentor_id' => $assignment->mentor_id]);
            });
        });

        return back()->with('success', 'Semester activated. Assigned students can now submit placements.');
    }

    public function close(InternshipCycle $semester)
    {
        abort_unless($semester->status === InternshipCycle::STATUS_ACTIVE, 409);

        $semester->update([
            'status' => InternshipCycle::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Semester closed. New submissions are now disabled.');
    }

    public function archive(InternshipCycle $semester)
    {
        abort_unless($semester->status === InternshipCycle::STATUS_CLOSED, 409);
        $semester->update(['status' => InternshipCycle::STATUS_ARCHIVED]);

        return redirect()->route('admin.semesters.index')
            ->with('success', 'Semester archived. Its reports remain available.');
    }

    private function validatedCycle(Request $request, ?InternshipCycle $cycle = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'intake_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('internship_cycles', 'intake_code')->ignore($cycle?->id),
            ],
            'academic_year' => ['required', 'string', 'max:20'],
            'placement_window_start' => ['required', 'date'],
            'placement_window_end' => ['required', 'date', 'after_or_equal:placement_window_start'],
            'duration_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'deadline_weekday' => ['nullable', 'integer', 'between:0,6'],
            'deadline_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'in:Asia/Singapore,Asia/Kuala_Lumpur,UTC'],
        ]) + [
            'deadline_weekday' => 5,
            'deadline_time' => '23:59',
        ];
    }
}
