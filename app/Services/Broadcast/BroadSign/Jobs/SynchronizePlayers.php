<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizePlayers.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Player as BSPlayer;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises players from the Broadsign API to the Network DB. New players are added, missing ones are
 * removed and other gets updated if necessary. Players gets associated with their proper location, therefore, it is
 * recommended to run the `SynchroniseLocations` job before running this one.
 *
 * @package Neo\Jobs
 */
class SynchronizePlayers extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): int {
        return $this->config->networkID;
    }

    public function handle(): void {
        $broadsignPlayers = BSPlayer::all($this->getAPIClient());
        $players          = [];

        $progressBar = $this->makeProgressBar(count($broadsignPlayers));
        $progressBar->start();

        /** @var BSPlayer $bsPlayer */
        foreach ($broadsignPlayers as $bsPlayer) {
            $progressBar->advance();
            $progressBar->setMessage("$bsPlayer->name ($bsPlayer->id)");

            if (!$bsPlayer->active) {
                // Player is inactive, make sure it is not present in our DB

                // We ignore network identification here until the migration to thee new networks setup is done.
                // TODO: Add the network identification back.
                Player::query()->where('external_id', '=', $bsPlayer->id)
//                               ->where("network_id", "=", $this->config->networkID)
                      ->delete();
                continue;
            }

            // Check if the player match a location in the network
            $location = Location::query()
                                ->where("external_id", "=", $bsPlayer->display_unit_id)
//                                ->where("network_id", "=", $this->config->networkID)
                                ->first(["id"]);

            if ($location === null) {
                // No location uses this player, ignore
                continue;
            }

            /** @var Player $player */
            $player = Player::query()->firstOrCreate([
                "external_id" => $bsPlayer->id,
            ], [
                "location_id" => $location->id,
                "name"        => $bsPlayer->name,
            ]);

            $player->network_id = $this->config->networkID;
            $player->save();

            $players[] = $player->id;
        }

        $progressBar->setMessage("Players syncing done!");
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");

        // Erase missing players
        Player::query()
              ->whereNotIn("id", $players)
              ->where("network_id", "=", $this->config->networkID)
              ->delete();
    }
}
