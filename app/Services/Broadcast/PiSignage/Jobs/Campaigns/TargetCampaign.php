<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TargetCampaign.php
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
        // Make sure this job is delayed
//        $this->delay = 180;

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
        $playlist = Playlist::get($this->getAPIClient(), $campaign->external_id);

        if (!$playlist) {
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
        $playlist->save();

        $playlistAssets = collect($playlist->assets)->pluck("filename");
        $playlistFile   = "__$playlist->name.json";

        // Assigned the playlist to all desired locations and remove it from other
        $groups = Group::all($this->getAPIClient());


        /** @var Group $group */
        foreach ($groups as $group) {
            $playlistIsPresent = $group->hasPlaylist($playlist->name);
            $groupIsTargeted   = $groupIds->contains($group->getKey());

            if (!$playlistIsPresent && !$groupIsTargeted) {
                // we do not target this group, and the playlist is absent from it, ignore.
                continue;
            }

            $group->deploy                   = true;
            $group->loadPlaylistOnCompletion = true;
            $group->playAllEligiblePlaylists = true;

            if ($playlistIsPresent) {
                // We remove the playlist if it is present
                $group->playlists = collect($group->playlists)->filter(fn($p) => $p["name"] !== $playlist->name)->toArray();

                // We also remove the required assets
                $group->assets = collect($group->assets)
                    ->filter(fn($a) => !$playlistAssets->contains($a) && $a !== $playlistFile)
                    ->toArray();
            }

            if ($groupIsTargeted) {
                // This group is targeted, add the playlist
                $group->playlists[] = $playlist;


                // Add the playlist assets as well
                $group->assets[] = $playlistFile;
                $group->assets   = [...$group->assets, ...$playlistAssets->toArray()];
            }

            // Save
            $group->save();
        }
    }

}
