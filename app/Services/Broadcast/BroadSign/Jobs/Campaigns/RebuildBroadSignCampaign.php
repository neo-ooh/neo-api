<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RebuildBroadSignCampaign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Campaign;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\CreateBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\UpdateBroadSignScheduleStatus;

/**
 * Class DisableBroadSignCampaign
 * Disable a broadsign campaign, effectively stopping the broadcast of all its schedules
 *
 * @package Neo\Jobs
 */
class RebuildBroadSignCampaign extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the campaign
     */
    protected int $campaignsId;

    public function uniqueId(): int {
        return $this->campaignsId;
    }


    /**
     * Create a new job instance.
     *
     * @param int $campaignId ID of the campaign
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $campaignId) {
        parent::__construct($config);
        $this->campaignsId = $campaignId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->findOrFail($this->campaignsId);
        // Start by disabling all the schedules of the campaign in BroadSign
        /** @var Schedule $schedule */
        foreach ($campaign->schedules as $schedule) {
            if ($schedule->external_id_2 === null) {
                continue;
            }

            DisableBroadSignSchedule::dispatchSync($this->config, $schedule->external_id_2);
            $schedule->external_id_2 = null;
            $schedule->save();
        }

        // Now disable the campaign itself
        if ($campaign->external_id !== null) {
            DisableBroadSignCampaign::dispatchSync($this->config, $campaign->external_id);
            $campaign->external_id = null;
            $campaign->save();
        }

        // Now we create a brand new Campaign
        CreateBroadSignCampaign::dispatchSync($this->config, $campaign->id);

        // Pull the newly created BroadSign Campaign
        $campaign->refresh();

        // Re-create all the schedules in BroadSign
        /** @var Schedule $schedule */
        foreach ($campaign->schedules as $schedule) {
            CreateBroadSignSchedule::dispatchSync($this->config, $schedule->id, $schedule->owner_id);
            UpdateBroadSignScheduleStatus::dispatchSync($this->config, $schedule->id);
        }

        // Done.
    }
}
