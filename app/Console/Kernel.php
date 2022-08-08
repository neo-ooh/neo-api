<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Kernel.php
 */

namespace Neo\Console;

use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Neo\Console\Commands\PullPropertyTraffic;
use Neo\Jobs\Contracts\ClearOldScreenshots;
use Neo\Jobs\Contracts\RefreshContracts;
use Neo\Jobs\NotifyEndOfSchedules;
use Neo\Jobs\Odoo\SynchronizeProperties;
use Neo\Jobs\Properties\CreateTrafficSnapshotJob;
use Neo\Jobs\RequestScreenshotsBursts;
use Neo\Jobs\Schedules\DisableExpiredSchedulesJob;
use Neo\Jobs\Traffic\FillMissingTrafficValueJob;
use Neo\Jobs\Traffic\PullLatestTrafficData;
use Neo\Jobs\Traffic\TrafficRequiredReminder;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // properties:sync
        SynchronizeProperties::class,

        // contracts:update
        RefreshContracts::class,

        // contracts:clear-screenshots
        ClearOldScreenshots::class,

        // property:pull-traffic {property}
        PullPropertyTraffic::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void {
        /* -----------------
         * Every-minute tasks
         */

        // Send screenshots requests to player
        $schedule->job(RequestScreenshotsBursts::class)->everyMinute();


        /* -----------------
         * "More often that hourly but not every minutes"
         */
        /*        $schedule->call(function (NewsService $news) {
                    $news->updateRecords();
                })->everyFifteenMinutes();*/


        /* -----------------
         * Hourly tasks
         */

        // Refresh Contracts performances
        $schedule->command("contracts:update")->everyThreeHours();


        /* -----------------
         * Daily tasks
         */

        // Take a snapshot of properties' traffic
        $schedule->job(CreateTrafficSnapshotJob::class)->dailyAt("01:00");

        $schedule->command('properties:sync')->daily();
        $schedule->command('contracts:clear-screenshots')->daily();

        // Try scheduling jobs that have not been scheduled properly
//        $schedule->job(RetrySchedulesJob::class)->daily();

        // End of schedule email
        $schedule->job(NotifyEndOfSchedules::class)->weekdays()
                 ->timezone('America/Toronto')
                 ->at("06:00");


        /* -----------------
         * Monthly tasks
         */

        // Pull traffic data for property with Linkett pairing
        $schedule->job(PullLatestTrafficData::class)->monthly();
        $schedule->job(FillMissingTrafficValueJob::class)->monthlyOn(2);

        // Send Reminder about traffic data to users
        $schedule->job(TrafficRequiredReminder::class)->monthlyOn(7);

        // Input last month traffic value were missing
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return DateTimeZone|string|null
     */
    protected function scheduleTimezone(): DateTimeZone|string|null {
        return 'America/Toronto';
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void {
        $this->load(__DIR__ . '/Commands');
    }
}
