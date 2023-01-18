<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastKernelServiceProvider.php
 */

namespace Neo\Modules\Broadcast\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Neo\Modules\Broadcast\Jobs\Chores\DeleteExpiredResourcesJob;
use Neo\Modules\Broadcast\Jobs\Chores\RetryPendingBroadcastJobsJob;
use Neo\Modules\Broadcast\Jobs\Networks\SynchronizeAllNetworksJob;
use Neo\Modules\Broadcast\Jobs\Performances\FetchCampaignsPerformancesJob;

class BroadcastKernelServiceProvider extends ServiceProvider {
    public function register() {
    }

    public function boot() {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // Networks
            $schedule->job(SynchronizeAllNetworksJob::class)->daily();

            // Resources
            $schedule->job(DeleteExpiredResourcesJob::class)->daily();

            // Campaigns Performances
            $schedule->job(FetchCampaignsPerformancesJob::class)->daily();

            // Non executed broadcast jobs
            $schedule->job(RetryPendingBroadcastJobsJob::class)->daily();
        });
    }
}
