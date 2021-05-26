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
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
 * @package Neo\Jobs
 */
class SetCampaignSchedules extends PiSignageJob implements ShouldBeUnique {
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
        // Since schedules have no actual representation in PiSignage, we update the list of assets in the playlist to represent active assets

        /** @var ?Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignId);

        if (!$campaign) {
            // No campaign
            return;
        }

        if (!$campaign->external_id) {
            // Campaign is not replicated yet
            $this->release(60);
            return;
        }

        $playlist = Playlist::get($this->getAPIClient(), $campaign->external_id);

        if(!$playlist) {
            return;
        }

        // Build the asset array
        $assetArray = [];

        /** @var Schedule $schedule */
        foreach ($campaign->schedules as $schedule) {
            if (!$schedule->is_approved) {
                // Schedule is not approved, ignore
                continue;
            }

            // As of now, we only support scheduling files one by one
            // TODO: Add support for multiple creatives/frames

            /** @var Creative $creative */
            $creative = $schedule->content->creatives()->first();

            $assetName = $schedule->id . "@" . $creative->id . "." . $creative->properties->extension;

            $assetArray[] = [
                "filename" => $assetName,
                "duration" => $schedule->campaign->display_duration,
                "isVideo"  => $creative->properties->extension === "mp4",
                "selected" => false,
                "option"   => [
                    "main" => false, // True => Mute sound; False => Don't mute
                ]
            ];
        }

        $playlist->assets = $assetArray;
        $playlist->save();

        TargetCampaign::dispatch($this->config, $campaign->id);
    }
}
