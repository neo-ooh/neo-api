<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateBroadSignCampaign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Campaign as BSCampaign;

/**
 * Class CreateBroadSignCampaign
 * Create the BroadSign campaign matching the Access' one. BS Campaigns are created with a far-future end date. This is
 * to prevent rebooking, which as of now, 2020-10, cannot be done automatically. Actual end date of the campaign is
 * handled inside Direct
 *
 * @package Neo\Jobs
 */
class CreateBroadSignCampaign extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignID;

    public function uniqueId(): int {
        return $this->campaignID;
    }

    /**
     * Create a new job instance.
     *
     * @param int $campaignID ID of the campaign created on Access
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $campaignID) {
        parent::__construct($config);

        $this->campaignID = $campaignID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        // Get the access campaign
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignID);

        if ($campaign === null || $campaign->external_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Create the campaign
        $bsCampaign                           = new BSCampaign($this->getAPIClient());
        $bsCampaign->auto_synchronize_bundles = true;
        $bsCampaign->container_id             = $this->config->reservationsContainerId;
        $bsCampaign->duration_msec            = $campaign->schedules_default_length * 1000;
        $bsCampaign->end_date                 = $campaign->end_date->toDateString();
        $bsCampaign->end_time                 = $campaign->end_date->toTimeString();
        $bsCampaign->name                     = $campaign->owner->name . " - " . $campaign->name;
        $bsCampaign->parent_id                = $this->config->customerId;
        $bsCampaign->start_date               = $campaign->start_date->toDateString();
        $bsCampaign->start_time               = $campaign->start_date->toTimeString();
        $bsCampaign->saturation               = $campaign->loop_saturation > 0
            ? $campaign->loop_saturation
            : $campaign->schedules->filter(fn($schedule) => $schedule->is_approved)->count();
        $bsCampaign->priority                 = $campaign->priority;
        $bsCampaign->default_fullscreen       = false;
        $bsCampaign->create();

        // Save the BroadSign campaign ID with the Access campaign
        $campaign->external_id = $bsCampaign->id;
        $campaign->save();

        // Now set the targeting of the campaign
        TargetCampaign::dispatch($this->config, $campaign->id);
    }
}
