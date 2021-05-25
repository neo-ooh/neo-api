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
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Campaign;
use Neo\Models\DisplayType;
use Neo\Models\Location;
use Neo\Services\Broadcast\BroadSign\Models\Container;
use Neo\Services\Broadcast\BroadSign\Models\Format;
use Neo\Services\Broadcast\BroadSign\Models\Location as BSLocation;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Group;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class TargetCampaign extends PiSignageJob implements ShouldBeUnique {
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
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignId);

        if ($campaign === null || $campaign->external_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Get the playlist in PiSignage representing the campaign
        $playlist = Playlist::get($this->getAPIClient(), $campaign->external_id);
        $groupIds = $campaign->locations->pluck("external_id");

        // Assigned the playlist to all desired locations and remove it from other
        $groups = Group::all($this->getAPIClient());

        /** @var Group $group */
        foreach ($groups as $group) {
            $playlistIsPresent = $group->hasPlaylist($playlist->name);
            $groupIsTargeted = $groupIds->contains($group->getKey());

            if(($playlistIsPresent && $groupIsTargeted) || (!$playlistIsPresent && !$groupIsTargeted)) {
                continue;
            }

            if($groupIsTargeted && !$playlistIsPresent) {
                // Add the playlist
                $group->playlists[] = $playlist;
            }

            if(!$groupIsTargeted && $playlist) {
                // Remove the playlist
                $group->playlists = collect($group->playlists)->filter(fn($p) => $p["name"] !== $playlist->name);
            }

            $group->save();
        }
    }

}
