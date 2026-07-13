<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hoursByStudentAndWeek = [
            'adam@gmail.com' => [1 => 40, 3 => 38],
            'aisha@gmail.com' => [1 => 35, 2 => 40],
            'daniel@gmail.com' => [1 => 40, 2 => 39, 3 => 40],
        ];

        foreach ($hoursByStudentAndWeek as $email => $weeks) {
            $studentId = DB::table('users')->where('email', $email)->value('id');
            if (! $studentId) {
                continue;
            }

            foreach ($weeks as $week => $hours) {
                $logbook = DB::table('logbooks')
                    ->where('user_id', $studentId)
                    ->where('week_number', $week)
                    ->whereNotNull('description')
                    ->first(['id', 'start_date']);

                if (! $logbook) {
                    continue;
                }

                $totalMinutes = $hours * 60;
                $minutesPerDay = intdiv($totalMinutes, 5);
                $remainder = $totalMinutes % 5;
                $startDate = Carbon::parse($logbook->start_date);
                $attendance = collect(range(0, 4))->map(fn (int $day): array => [
                    'date' => $startDate->copy()->addDays($day)->toDateString(),
                    'status' => 'present',
                    'rendered_minutes' => $minutesPerDay + ($day < $remainder ? 1 : 0),
                    'note' => null,
                    'mc_evidence_path' => null,
                ])->all();

                DB::table('logbooks')->where('id', $logbook->id)->update([
                    'rendered_minutes' => $totalMinutes,
                    'attendance_entries' => json_encode($attendance),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Do not remove attendance data that participants might have edited.
    }
};
