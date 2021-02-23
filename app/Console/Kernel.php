<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Kernel.php
 */

namespace Neo\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Neo\BroadSign\Jobs\Players\RequestScreenshotsBursts;
use Neo\Jobs\RefreshReportReservations;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        NetworkUpdate::class,
        RebuildResources::class,

        Hotfixes\DisableFullscreenEverywhere::class,
        Hotfixes\RetargetAllCampaigns::class,
        Hotfixes\RetargetAllCreatives::class,
        Hotfixes\RecreateAllCampaigns::class,

        Chores\CleanUpCampaigns::class,
        Chores\CleanUpCreatives::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->command('network:update')->daily();
        $schedule->job(RequestScreenshotsBursts::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        $this->command("reports:refresh", function($reportId) {
            RefreshReportReservations::dispatchSync($reportId);
        });

        require base_path('routes/console.php');
    }
}
