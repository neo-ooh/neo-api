<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RebuildBroadSignCampaign.php
 */

namespace Neo\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Jobs\Schedules\CreateBroadSignSchedule;
use Neo\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;
use Neo\BroadSign\Jobs\Schedules\UpdateBroadSignScheduleStatus;
use Neo\Models\Campaign;
use Neo\Models\Schedule;

/**
 * Class DisableBroadSignCampaign
 * Disable a broadsign campaign, effectively stopping the broadcast of all its schedules
 *
 * @package Neo\Jobs
 */
class RebuildBroadSignCampaign extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the campaign
     */
    protected int $campaignsId;


    /**
     * Create a new job instance.
     *
     * @param int $campaignId ID of the campaign
     *
     * @return void
     */
    public function __construct(int $campaignId) {
        $this->campaignsId = $campaignId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        $campaign = Campaign::findOrFail($this->campaignsId);
        // Start by disabling all the schedules of the campaign in BroadSign
        /** @var Schedule $schedule */
        foreach ($campaign->schedules as $schedule) {
            if ($schedule->broadsign_schedule_id === null) {
                continue;
            }

            DisableBroadSignSchedule::dispatchSync($schedule->broadsign_schedule_id);
            $schedule->broadsign_schedule_id = null;
            $schedule->save();
        }

        // Now disable the campaign itself
        if ($campaign->broadsign_reservation_id !== null) {
            DisableBroadSignCampaign::dispatchSync($campaign->broadsign_reservation_id);
            $campaign->broadsign_reservation_id = null;
            $campaign->save();
        }

        // Now we create a brand new Campaign
        CreateBroadSignCampaign::dispatchSync($campaign->id);

        // Pull the newly created BroadSign Campaign
        $campaign->refresh();

        // Re-create all the schedules in BroadSign
        /** @var Schedule $schedule */
        foreach ($campaign->schedules as $schedule) {
            CreateBroadSignSchedule::dispatchSync($schedule->id, $schedule->owner_id);
            UpdateBroadSignScheduleStatus::dispatchSync($schedule->id);
        }

        // Done.
    }
}
