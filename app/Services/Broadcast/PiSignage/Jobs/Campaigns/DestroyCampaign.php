<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyCampaign.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Campaigns;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Services\PiSignage\Models\Group;
use Neo\Modules\Broadcast\Services\PiSignage\Models\Playlist;
use Neo\Modules\Broadcast\Services\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;

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

    public function handle(): void {
        /** @var \Neo\Modules\Broadcast\Models\Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null || !$campaign->external_id) {
            // this campaign either doesn't exist or doesn't have a reservation ID, do nothing.
            return;
        }

        // We want to delete the playlist AND deploy all the configuration of all associated players so they are up to date.
        Playlist::delete($this->getAPIClient(), ["name" => $campaign->external_id]);

        /** @var \Neo\Modules\Broadcast\Models\Location $location */
        foreach ($campaign->locations as $location) {
            $group            = Group::get($this->getAPIClient(), $location->external_id);
            $group->playlists = collect($group->playlists)->filter(fn($playlist) => $playlist["name"] !== $campaign->external_id);
            $group->deploy    = true;
            $group->save();
        }

        $campaign->external_id = null;
        $campaign->save();
        // Done!
    }

}
