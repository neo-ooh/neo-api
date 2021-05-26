<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Campaigns;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Campaign;
use Neo\Models\Location;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Group;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
 * @package Neo\Jobs
 */
class DestroyCampaign extends PiSignageJob implements ShouldBeUnique {
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

        if ($campaign === null || !$campaign->external_id) {
            // this campaign either doesn't exist or doesn't have a reservation ID, do nothing.
            return;
        }

        // We want to delete the playlist AND deploy all the configuration of all associated players so they are up to date.
        Playlist::delete($this->getAPIClient(), ["name" => $campaign->external_id]);
        $campaign->external_id = null;
        $campaign->save();

        /** @var Location $location */
        foreach ($campaign->locations as $location) {
            $group = Group::get($this->getAPIClient(), $location->external_id);
            $group->deploy = true;
            $group->save();
        }

        // Done!
    }

}
