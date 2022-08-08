<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ForceRefreshPlaylist.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\DisplayUnits;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Player as BSPlayer;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class ForceRefreshPlaylist extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(BroadSignConfig $config, protected int $propertyId) {
        parent::__construct($config);
    }

    public function uniqueId(): int {
        return $this->propertyId;
    }

    public function handle(): void {
        $location = Location::query()->with("players")->find($this->propertyId);

        /** @var \Neo\Modules\Broadcast\Models\Player $player */
        foreach ($location->players as $player) {
            $bsPlayer = new BSPlayer($this->getAPIClient(), ["id" => $player->id]);
            $bsPlayer->forceUpdatePlaylist();
        }
    }
}
