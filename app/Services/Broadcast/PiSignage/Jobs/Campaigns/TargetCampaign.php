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
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Group;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
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

        if ($campaign === null || !$campaign->external_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Get the playlist in PiSignage representing the campaign
        $playlist       = Playlist::get($this->getAPIClient(), $campaign->external_id);

        if(!$playlist) {
            return;
        }

        $playlist->name = $campaign->external_id;
        $groupIds       = $campaign->locations->pluck("external_id");

        // Configure the playlist
        $playlist->settings["plType"]         = "regular";
        $playlist->settings["durationEnable"] = true;
        $playlist->settings["startdate"]      = $campaign->start_date->toDateString();
        $playlist->settings["enddate"]        = $campaign->end_date->toDateString();
        $playlist->settings["timeEnable"]     = true;
        $playlist->settings["starttime"]      = $campaign->start_date->toTimeString();
        $playlist->settings["endtime"]        = $campaign->end_date->toTimeString();

        // Assigned the playlist to all desired locations and remove it from other
        $groups = Group::all($this->getAPIClient());

        /** @var Group $group */
        foreach ($groups as $group) {
            $playlistIsPresent = $group->hasPlaylist($playlist->name);
            $groupIsTargeted   = $groupIds->contains($group->getKey());

            $group->deploy                   = true;
            $group->playAllEligiblePlaylists = true;

            if ((!$playlistIsPresent && !$groupIsTargeted)) {
                // we do not target this group, and the playlist is absent from it, ignore.
                continue;
            }

            if ($playlistIsPresent) {
                // We remove the playlist if it is present, even if we target the group, as we will re-insert it after to update it.
                $group->playlists = collect($group->playlists)->filter(fn($p) => $p["name"] !== $playlist->name);
            }

            if (!$groupIsTargeted) {
                // No playlist in group, and group not targeted, stop here.
                $group->save();
                continue;
            }

            // Add the playlist
            $group->playlists[] = $playlist;
            $group->save();
        }
    }

}