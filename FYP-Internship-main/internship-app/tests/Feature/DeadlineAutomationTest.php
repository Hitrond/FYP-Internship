<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\User;
use App\Services\PlacementTimelineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DeadlineAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_opens_and_locks_weeks_around_deadlines_and_extensions(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-06 12:00:00');

        $student = User::factory()->create(['role' => 'student']);
        $timeline = app(PlacementTimelineService::class);

        $future = $this->logbook($student, 1, [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(4)->toDateString(),
            'submission_due_at' => now()->addWeeks(2),
            'status' => 'open',
        ]);

        $current = $this->logbook($student, 2, [
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'submission_due_at' => now()->addWeek(),
            'status' => 'scheduled',
        ]);

        $missed = $this->logbook($student, 3, [
            'start_date' => now()->subWeeks(2)->toDateString(),
            'end_date' => now()->subWeek()->toDateString(),
            'submission_due_at' => now()->subMinute(),
            'status' => 'open',
        ]);

        $extended = $this->logbook($student, 4, [
            'start_date' => now()->subWeeks(2)->toDateString(),
            'end_date' => now()->subWeek()->toDateString(),
            'submission_due_at' => now()->subWeek(),
            'status' => 'overdue_locked',
            'locked_at' => now()->subDay(),
            'extension_status' => 'approved',
            'extension_until' => now()->addDay(),
        ]);

        $this->assertSame('scheduled', $timeline->sync($future)->status);
        $this->assertSame('open', $timeline->sync($current)->status);
        $this->assertSame('overdue_locked', $timeline->sync($missed)->status);
        $this->assertSame('open', $timeline->sync($extended)->status);
        $this->assertNull($extended->fresh()->locked_at);

        Carbon::setTestNow('2026-07-08 12:00:00');

        $this->assertSame('overdue_locked', $timeline->sync($extended->fresh())->status);
        $this->assertNotNull($extended->fresh()->locked_at);

        Carbon::setTestNow();
    }

    private function logbook(User $student, int $week, array $attributes): Logbook
    {
        return Logbook::create(array_merge([
            'user_id' => $student->id,
            'week_number' => $week,
            'timeline_generated' => true,
        ], $attributes));
    }
}
