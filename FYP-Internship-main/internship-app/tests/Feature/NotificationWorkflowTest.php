<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use App\Notifications\WorkflowAlertNotification;
use App\Services\PlacementTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_logbook_notifies_student_and_academic_mentor_once(): void
    {
        Notification::fake();
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
        ]);
        $logbook = Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'timeline_generated' => true,
            'start_date' => now()->subWeeks(3)->startOfWeek(),
            'end_date' => now()->subWeeks(3)->startOfWeek()->addDays(4),
            'submission_due_at' => now()->subWeek(),
            'status' => 'open',
            'description' => null,
            'rendered_minutes' => 0,
        ]);

        app(PlacementTimelineService::class)->sync($logbook);
        app(PlacementTimelineService::class)->sync($logbook->fresh());

        $this->assertSame('overdue_locked', $logbook->fresh()->status);
        $this->assertNotNull($logbook->fresh()->overdue_notified_at);

        Notification::assertSentToTimes($student, WorkflowAlertNotification::class, 1);
        Notification::assertSentToTimes($mentor, WorkflowAlertNotification::class, 1);
    }

    public function test_extension_request_and_decision_notify_the_correct_people(): void
    {
        Notification::fake();
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
        ]);
        $logbook = Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'timeline_generated' => true,
            'start_date' => now()->subWeeks(3)->startOfWeek(),
            'end_date' => now()->subWeeks(3)->startOfWeek()->addDays(4),
            'submission_due_at' => now()->subWeek(),
            'locked_at' => now(),
            'overdue_notified_at' => now(),
            'status' => 'overdue_locked',
            'description' => null,
            'rendered_minutes' => 0,
        ]);

        $this->actingAs($student)
            ->post(route('student.logbook.extension.request', $logbook), [
                'extension_reason' => 'Medical recovery required additional time.',
            ])
            ->assertRedirect();

        Notification::assertSentTo(
            $mentor,
            WorkflowAlertNotification::class,
            fn ($notification) => str_contains($notification->title, 'Extension request')
        );

        $this->actingAs($mentor)
            ->patch(route('mentor.logbooks.extension.approve', $logbook), [
                'extension_until' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'extension_decision_note' => 'Approved for testing.',
            ])
            ->assertRedirect();

        Notification::assertSentTo(
            $student,
            WorkflowAlertNotification::class,
            fn ($notification) => str_contains($notification->title, 'extension approved')
        );
        $this->assertSame('open', $logbook->fresh()->status);
    }

    public function test_database_notification_centre_displays_and_marks_alert_as_read(): void
    {
        Mail::fake();
        $student = User::factory()->create(['role' => 'student']);
        $student->notifyNow(new WorkflowAlertNotification(
            'Test workflow alert',
            'This is a test notification.',
            route('dashboard'),
            'warning',
        ));
        $notification = $student->unreadNotifications()->firstOrFail();

        $this->actingAs($student)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Test workflow alert');

        $this->actingAs($student)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
