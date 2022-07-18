<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizePlayers.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Services\Broadcast\PiSignage\Models\Player as PiSignagePlayer;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class SynchronizePlayers extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $parsedLocations = [];

    public function uniqueId(): int {
        return $this->config->networkID;
    }

    public function handle(): void {
        (new ConsoleOutput())->writeLn("Synchronizing players {$this->config->networkUUID}...\n\n");

        $distPlayers = PiSignagePlayer::all($this->getAPIClient());

        $players = [];

        /** @var PiSignagePlayer $distPlayer */
        foreach ($distPlayers as $distPlayer) {
            // Ignore players without a name
            if (!$distPlayer->name) {
                continue;
            }

            (new ConsoleOutput())->writeLn("$distPlayer->name...\n\n");

            // Get the location associated with the player
            /** @var \Neo\Modules\Broadcast\Models\Location $location */
            $location = Location::query()->where("network_id", "=", $this->config->networkID)
                                ->where("external_id", "=", $distPlayer->group['_id'])
                                ->first();

            // Ignore player if no location can be found
            if ($location === null) {
                continue;
            }


            // Get / build the player in Connect's db
            $player = Player::query()->firstOrCreate([
                "network_id"  => $this->config->networkID,
                "external_id" => $distPlayer->_id
            ], [
                "location_id" => $location->id,
                "name"        => $distPlayer->name
            ]);

            $players[] = $player->id;
        }

        Player::query()
              ->where("network_id", "=", $this->config->networkID)
              ->whereNotIn("id", $players)
              ->delete();
    }

}
