<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RecreateAllCampaigns.php
 */

namespace Neo\Console\Hotfixes;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\Campaigns\CreateBroadSignCampaign;
use Neo\BroadSign\Jobs\Campaigns\DisableBroadSignCampaign;
use Neo\BroadSign\Jobs\Schedules\CreateBroadSignSchedule;
use Neo\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;
use Neo\BroadSign\Jobs\Schedules\UpdateBroadSignScheduleStatus;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class RecreateAllCampaigns extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:2021-02-17';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate all campaigns and their Schedules in BroadSign';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // We start by loading all campaigns on connect who have a counterpart on BroadSign
        $campaigns = Campaign::query()->whereNotNull('external_id')->get();

        $progressBar = $this->makeProgressBar(count($campaigns));
        $progressBar->start();

        // Now for each campaign, we load its locations, remove them, and trigger a re-targeting
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $progressBar->advance();
            $progressBar->setMessage("$campaign->name (#$campaign->id) Removing Schedules...");

            // Start by disabling all the campaigns schedules in BroadSign
            /** @var \Neo\Modules\Broadcast\Models\Schedule $schedule */
            foreach ($campaign->schedules as $schedule) {
                if ($schedule->broadsign_schedule_id === null) {
                    continue;
                }

                DisableBroadSignSchedule::dispatchSync($schedule->broadsign_schedule_id);
                $schedule->broadsign_schedule_id = null;
                $schedule->save();
            }
            $progressBar->setMessage("$campaign->name (#$campaign->id) Removing Campaign...");

            // Now disable the campaign itself
            DisableBroadSignCampaign::dispatchSync($campaign->external_id);
            $campaign->external_id = null;
            $campaign->save();

            $progressBar->setMessage("$campaign->name (#$campaign->id) Creating new Campaign...");

            // Now we create a brand new Campaign
            CreateBroadSignCampaign::dispatchSync($campaign->id);

            // Pull the newly created BroadSign Campaign
            $campaign->refresh();

            $progressBar->setMessage("$campaign->name (#$campaign->id) Creating new Schedules...");

            // Re-create all the schedules in BroadSign
            /** @var Schedule $schedule */
            foreach ($campaign->schedules as $schedule) {
                CreateBroadSignSchedule::dispatchSync($schedule->id, $schedule->owner_id);
                UpdateBroadSignScheduleStatus::dispatchSync($schedule->id);
            }
        }

        $progressBar->setMessage("Hotfix done");
        $progressBar->finish();

        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}