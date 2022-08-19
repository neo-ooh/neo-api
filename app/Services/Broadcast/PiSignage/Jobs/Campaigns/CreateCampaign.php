<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateCampaign.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Campaigns;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Services\PiSignage\Models\Playlist;
use Neo\Modules\Broadcast\Services\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;

/**
 * @package Neo\Jobs
 */
class CreateCampaign extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignId;

    public function uniqueId(): int {
        return $this->campaignId;
    }

    public function __construct(PiSignageConfig $config, int $campaignId) {
        parent::__construct($config);
        $this->campaignId = $campaignId;
    }

    protected function getCampaignNameInPiSignage(Campaign $campaign) {
        return "connect_" . $campaign->id . " - " . $campaign->name . "@" . $campaign->owner->name;
    }

    public function handle(): void {
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null || $campaign->external_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Create the playlist in PiSignage representing the campaign
        $playlist = Playlist::make($this->getAPIClient(), $this->getCampaignNameInPiSignage($campaign));

        // Configure the playlist

        $campaign->external_id = $playlist->name;
        $campaign->save();

        // Now, target the campaign
        TargetCampaign::dispatchSync($this->config, $this->campaignId);
    }

}
