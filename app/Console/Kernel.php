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

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Neo\Jobs\NotifyEndOfSchedules;
use Neo\Jobs\RefreshContractsReservations;
use Neo\Jobs\RequestScreenshotsBursts;
use Neo\Jobs\SynchronizeNetworks;
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

        RebuildResources::class,
        CacheInventory::class,

        Hotfixes\DisableFullscreenEverywhere::class,
        Hotfixes\RetargetAllCampaigns::class,
        Hotfixes\RetargetAllCreatives::class,
        Hotfixes\RecreateAllCampaigns::class,

        Chores\CleanUpCampaigns::class,
        Chores\CleanUpCreatives::class,

        Utils\MergeOTGResourcesIntoOneFormat::class,
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



        /* -----------------
         * Daily tasks
         */

        // Update network from broadsign
        $schedule->command('network:sync')->daily();

        // Refresh Contracts reservations
        $schedule->job(RefreshContractsReservations::class)->daily();

        // End of schedule email
        $schedule->job(NotifyEndOfSchedules::class)->weekdays()
                                                   ->timezone('America/Toronto')
                                                   ->at("06:00");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
