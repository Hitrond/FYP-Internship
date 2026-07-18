<?php

namespace App\Services;

use App\Models\Logbook;
use App\Models\PlacementClearance;
use App\Notifications\WorkflowAlertNotification;
use Carbon\Carbon;
use Throwable;

class PlacementTimelineService
{
    public function generate(PlacementClearance $placement): void
    {
        if (! $placement->start_date || ! $placement->end_date) {
            return;
        }

        $placement->loadMissing('cycle');
        $weekStart = $placement->start_date->copy()->startOfDay();
        $durationWeeks = $placement->cycle?->duration_weeks ?? 16;

        for ($week = 1; $week <= $durationWeeks; $week++) {
            $weekEnd = $weekStart->copy();
            while (! $weekEnd->isFriday()) {
                $weekEnd->addDay();
            }

            $dueAt = $this->deadlineFor($weekEnd, $placement);
            $status = now()->lt($weekStart)
                ? 'scheduled'
                : (now()->gt($dueAt) ? 'overdue_locked' : 'open');

            $logbook = Logbook::firstOrNew([
                'user_id' => $placement->student_id,
                'week_number' => $week,
            ]);
            $logbook->internship_cycle_id = $placement->internship_cycle_id;

            if (! $logbook->exists || ! $logbook->description) {
                $logbook->start_date = $weekStart->toDateString();
                $logbook->end_date = $weekEnd->toDateString();
                $logbook->status = $status;
                $logbook->submission_due_at = $dueAt;
                $logbook->locked_at = $status === 'overdue_locked'
                    ? ($logbook->locked_at ?? now())
                    : null;
            } else {
                $logbook->submission_due_at ??= $dueAt;
            }

            $logbook->timeline_generated = true;
            $logbook->save();

            if ($logbook->status === 'overdue_locked') {
                $this->notifyOverdue($logbook);
            }

            $weekStart = $weekEnd->copy()->next(Carbon::MONDAY)->startOfDay();
        }
    }

    public function sync(Logbook $logbook): Logbook
    {
        if (
            ! $logbook->timeline_generated
            || in_array($logbook->status, ['pending', 'approved', 'rejected'], true)
        ) {
            return $logbook;
        }

        $deadline = $logbook->extension_status === 'approved' && $logbook->extension_until
            ? $logbook->extension_until
            : $logbook->submission_due_at;

        if ($deadline && now()->gt($deadline)) {
            $logbook->status = 'overdue_locked';
            $logbook->locked_at ??= now();
        } elseif (now()->gte($logbook->start_date->startOfDay())) {
            $logbook->status = 'open';
            $logbook->locked_at = null;
        } else {
            $logbook->status = 'scheduled';
            $logbook->locked_at = null;
        }

        $logbook->save();

        if ($logbook->status === 'overdue_locked') {
            $this->notifyOverdue($logbook);
        }

        return $logbook;
    }

    private function notifyOverdue(Logbook $logbook): void
    {
        if ($logbook->overdue_notified_at) {
            return;
        }

        $logbook->loadMissing('student.mentor');
        $student = $logbook->student;

        if (! $student) {
            return;
        }

        $this->notifySafely(
            $student,
            new WorkflowAlertNotification(
                'Week '.$logbook->week_number.' logbook is overdue',
                'Your submission deadline has passed. Request an extension from your Academic Mentor to unlock this week.',
                route('student.logbook.index'),
                'danger',
            )
        );
        $this->notifySafely(
            $student->mentor,
            new WorkflowAlertNotification(
                'Overdue logbook alert: '.$student->name,
                $student->name.' missed the Week '.$logbook->week_number.' submission deadline.',
                route('mentor.dashboard'),
                'danger',
            )
        );

        $logbook->forceFill(['overdue_notified_at' => now()])->saveQuietly();
    }

    private function notifySafely(?object $recipient, WorkflowAlertNotification $notification): void
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

    private function deadlineFor(Carbon $weekEnd, PlacementClearance $placement): Carbon
    {
        $cycle = $placement->cycle;
        $deadlineWeekday = $cycle?->deadline_weekday ?? Carbon::FRIDAY;
        $deadlineTime = $cycle?->deadline_time
            ? Carbon::parse($cycle->deadline_time)->format('H:i:s')
            : '23:59:59';

        [$hour, $minute, $second] = array_pad(explode(':', $deadlineTime), 3, 0);

        return $weekEnd->copy()
            ->next((int) $deadlineWeekday)
            ->setTime((int) $hour, (int) $minute, (int) $second);
    }
}
