<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateBroadSignCampaign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Neo\Models\Campaign;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Bundle as BSBundle;
use Neo\Services\Broadcast\BroadSign\Models\Campaign as BSCampaign;

/**
 * Class UpdateBroadSignCampaign
 * We update the broadsign campaign to reflect the changes made on direct
 *
 * @package Neo\Jobs
 */
class UpdateBroadSignCampaign extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignID;


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
        // Get the Access and Broadsign campaign
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->findOrFail($this->campaignID);

        if ($campaign->external_id === null) {
            // This campaign has no BroadSign ID. It must be created before it gets updated
            CreateBroadSignCampaign::dispatch($this->config, $this->campaignID);

            // We die here as the creation triggers an update
            return;
        }

        $bsCampaign = BSCampaign::get($this->getAPIClient(), $campaign->external_id);

        $saturation = $campaign->loop_saturation > 0
            ? $campaign->loop_saturation
            : $campaign->schedules->filter(fn($schedule) => $schedule->is_approved)->count();

        // Can we simply update the BroadSign Campaign or do we need to rebuild it ?
        if ($saturation !== $bsCampaign->saturation
            || $campaign->start_date->notEqualTo(Date::make($bsCampaign->start_date))
            || $campaign->end_date->notEqualTo(Date::make($bsCampaign->end_date))) {
            // We need to rebuild the campaign
            RebuildBroadSignCampaign::dispatchSync($this->config, $campaign->id);
            return;
        }

        // Update the name and fullscreen status of the campaign
        $bsCampaign->name = $campaign->owner->name . " - " . $campaign->name;
        $bsCampaign->save();

        // Update the bundle in the campaign to match the campaign duration
        $bundles = BSBundle::byReservable($this->getAPIClient(), $bsCampaign->id);

        /** @var BSBundle $bundle */
        foreach ($bundles as $bundle) {
            $bundle->max_duration_msec = $campaign->display_duration * 1000; // ms
            $bundle->save();
        }
    }
}
