<?php

use App\Models\Logbook;
use App\Models\PlacementClearance;
use App\Models\User;
use App\Services\PlacementTimelineService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sus:seed-if-empty', function () {
    if (
        User::where('email', 'adam@gmail.com')->exists()
        && User::where('email', 'dhayanandahnaidu@gmail.com')->exists()
        && User::where('email', 'gobi@gmail.com')->exists()
        && User::where('email', 'james@crs.com')->exists()
        && User::where('email', 'admin@admin.com')->exists()
    ) {
        $this->info('SUS data already exists; leaving participant changes intact.');

        return self::SUCCESS;
    }

    Artisan::call('db:seed', ['--class' => 'SusUsabilitySeeder', '--force' => true]);
    $this->info('Initial SUS usability data created.');

    return self::SUCCESS;
})->purpose('Seed the temporary SUS environment once without resetting participant work');

Artisan::command('logbooks:sync-timeline', function (PlacementTimelineService $timeline) {
    Logbook::where('timeline_generated', true)
        ->whereIn('status', ['scheduled', 'open', 'overdue_locked'])
        ->chunkById(200, function ($logbooks) use ($timeline) {
            $logbooks->each(fn (Logbook $logbook) => $timeline->sync($logbook));
        });

    $this->info('Logbook timeline statuses synchronized.');
})->purpose('Open scheduled weeks and lock overdue logbooks');

Artisan::command('logbooks:generate-placement {placement}', function (
    PlacementTimelineService $timeline
) {
    $placement = PlacementClearance::findOrFail((int) $this->argument('placement'));

    if (! in_array($placement->status, ['approved', 'completed'], true)) {
        $this->error('The placement must be approved or completed before timeline generation.');

        return self::FAILURE;
    }

    if (! $placement->start_date || ! $placement->end_date) {
        $this->error('The placement must have official start and end dates.');

        return self::FAILURE;
    }

    $timeline->generate($placement);
    $this->info("Generated the placement timeline for student {$placement->student_id}.");

    return self::SUCCESS;
})->purpose('Generate or recover the 16-week timeline for an approved placement');

Schedule::command('logbooks:sync-timeline')
    ->hourly()
    ->withoutOverlapping();
