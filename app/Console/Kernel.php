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
use Neo\Console\Commands\CacheInventory;
use Neo\Console\Commands\Properties\PushAllPropertiesTrafficCommand;
use Neo\Console\Commands\PullPropertyTraffic;
use Neo\Jobs\Contracts\ClearOldScreenshots;
use Neo\Jobs\Creatives\RemoveUnusedCreativesFromBroadcasterJob;
use Neo\Jobs\Maintenance\RetrySchedulesJob;
use Neo\Jobs\NotifyEndOfSchedules;
use Neo\Jobs\Odoo\SynchronizeProperties;
use Neo\Jobs\RefreshAllContracts;
use Neo\Jobs\RequestScreenshotsBursts;
use Neo\Jobs\SynchronizeNetworks;
use Neo\Jobs\Traffic\FillMissingTrafficValueJob;
use Neo\Jobs\Traffic\PullLatestTrafficData;
use Neo\Jobs\Traffic\TrafficRequiredReminder;
use Neo\Services\News\NewsService;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // network:sync
        SynchronizeNetworks::class,

        // properties:sync
        SynchronizeProperties::class,

        // properties:push-traffic
        PushAllPropertiesTrafficCommand::class,

        // network:rebuild
        RebuildResources::class,

        // network:cache-inventory
        CacheInventory::class,

        // network:update-contracts
        RefreshAllContracts::class,

        // contracts:clear-screenshots
        ClearOldScreenshots::class,

        // hotfix:...
        Hotfixes\RetargetAllCampaigns::class,
        Hotfixes\RetargetAllCreatives::class,
        Hotfixes\RecreateAllCampaigns::class,

        Utils\MergeOTGResourcesIntoOneFormat::class,

        PullPropertyTraffic::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        /* -----------------
         * Every-minute tasks
         */

        // Send screenshots requests to player
        $schedule->job(RequestScreenshotsBursts::class)->everyMinute();


        /* -----------------
         * "More often that hourly but not every minutes"
         */
        $schedule->call(function (NewsService $news) {
            $news->updateRecords();
        })->everyFifteenMinutes();


        /* -----------------
         * Hourly tasks
         */

        // Cache Broadsign inventory for fast access in Connect
        $schedule->command('network:cache-inventory')->everyThreeHours();

        // Refresh Contracts reservations
        $schedule->command('network:update-contracts')->everyThreeHours();


        /* -----------------
         * Daily tasks
         */

        // Update network from broadsign & others
        $schedule->command('network:sync')->daily();
        $schedule->command('properties:sync')->daily();
        $schedule->command('contracts:clear-screenshots')->daily();

        // Remove unused creatives from external broadcasters
        $schedule->job(RemoveUnusedCreativesFromBroadcasterJob::class)->daily();

        // Try scheduling jobs that have not been scheduled properly
        $schedule->job(RetrySchedulesJob::class)->daily();

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
    protected function scheduleTimezone() {
        return 'America/Toronto';
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');
    }
}
