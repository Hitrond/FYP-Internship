<?php

namespace Tests\Feature;

use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceEvaluationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_industrial_supervisor_can_save_submit_and_lock_evaluation(): void
    {
        [$student, $supervisor] = $this->assignedUsers();

        $this->actingAs($supervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'midterm']), $this->payload('draft'))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('supervisor.evaluations.index'));

        $this->assertDatabaseHas('performance_evaluations', [
            'student_id' => $student->id,
            'type' => 'midterm',
            'status' => 'draft',
        ]);

        $this->actingAs($supervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'midterm']), $this->payload('submit'))
            ->assertSessionHasNoErrors();

        $evaluation = PerformanceEvaluation::firstOrFail();
        $this->assertSame('submitted', $evaluation->status);
        $this->assertNotNull($evaluation->submitted_at);

        $this->actingAs($supervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'midterm']), $this->payload('draft'))
            ->assertStatus(409);
    }

    public function test_unassigned_supervisor_cannot_evaluate_student(): void
    {
        [$student] = $this->assignedUsers();
        $otherSupervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($otherSupervisor)
            ->get(route('supervisor.evaluations.edit', [$student, 'final']))
            ->assertForbidden();

        $this->actingAs($otherSupervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'final']), $this->payload('submit'))
            ->assertForbidden();
    }

    public function test_poor_rating_requires_a_comment(): void
    {
        [$student, $supervisor] = $this->assignedUsers();
        $payload = $this->payload('submit');
        $payload['ratings']['technical_knowledge'] = [
            'rating' => 'D',
            'comment' => '',
        ];

        $this->actingAs($supervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'final']), $payload)
            ->assertSessionHasErrors('ratings.technical_knowledge.comment');

        $this->assertDatabaseCount('performance_evaluations', 0);
    }

    public function test_student_and_academic_mentor_only_see_authorized_submitted_evaluations(): void
    {
        [$student, $supervisor, $mentor] = $this->assignedUsers();
        $otherStudent = User::factory()->create(['role' => 'student']);
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        $payload = $this->payload('submit');
        $payload['overall_grade'] = 3;
        $payload['overall_comments'] = 'Student requires additional workplace support.';

        $this->actingAs($supervisor)
            ->put(route('supervisor.evaluations.store', [$student, 'midterm']), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($student)
            ->get(route('student.evaluations.index'))
            ->assertOk()
            ->assertSee('Student requires additional workplace support.');

        $this->actingAs($otherStudent)
            ->get(route('student.evaluations.index'))
            ->assertOk()
            ->assertDontSee('Student requires additional workplace support.');

        $this->actingAs($mentor)
            ->get(route('mentor.evaluations.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Intervention recommended');

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Performance intervention recommended');

        $this->actingAs($otherMentor)
            ->get(route('mentor.evaluations.index'))
            ->assertOk()
            ->assertDontSee($student->name);
    }

    private function assignedUsers(): array
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
            'mentor_id' => $mentor->id,
        ]);

        return [$student, $supervisor, $mentor];
    }

    private function payload(string $action): array
    {
        $ratings = [];
        foreach (PerformanceEvaluation::CRITERIA as $key => $label) {
            $ratings[$key] = [
                'rating' => 'B',
                'comment' => 'Consistently meets workplace expectations.',
            ];
        }

        return [
            'action' => $action,
            'ratings' => $ratings,
            'overall_grade' => 8,
            'overall_comments' => 'Good progress and professional conduct.',
        ];
    }
}
