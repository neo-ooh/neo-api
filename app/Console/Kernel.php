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
use Neo\Console\Commands\CacheInventory;
use Neo\Console\Commands\PullPropertyTraffic;
use Neo\Jobs\NotifyEndOfSchedules;
use Neo\Jobs\Properties\PullLatestTrafficData;
use Neo\Jobs\RefreshAllContracts;
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

        // network:rebuild
        RebuildResources::class,

        // network:cache-inventory
        CacheInventory::class,

        // network:update-contracts
        RefreshAllContracts::class,

        // hotfix:...
        Hotfixes\DisableFullscreenEverywhere::class,
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

        // Update network from broadsign
        $schedule->command('network:sync')->daily();

        // End of schedule email
        $schedule->job(NotifyEndOfSchedules::class)->weekdays()
                                                   ->timezone('America/Toronto')
                                                   ->at("06:00");



        /* -----------------
         * Monthly tasks
         */

        // Pull traffic data for property with Linkett pairing
        $schedule->job(PullLatestTrafficData::class)->monthly();

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
