<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - SynchronizePlayers.php
 */

namespace Neo\BroadSign\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Player as BSPlayer;
use Neo\Models\Location;
use Neo\Models\Player;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises players from the Broadsign API to the Network DB. New players are added, missing ones are
 * removed and other gets updated if necessary. Players gets associated with their proper location, therefore, it is
 * recommended to run the `SynchroniseLocations` job before running this one.
 *
 * @package Neo\Jobs
 */
class SynchronizePlayers extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        $broadsignPlayers = BSPlayer::all();
        $players          = [];

        $progressBar = $this->makeProgressBar(count($broadsignPlayers));
        $progressBar->start();

        /** @var BSPlayer $bsPlayer */
        foreach ($broadsignPlayers as $bsPlayer) {
            $progressBar->advance();
            $progressBar->setMessage("{$bsPlayer->name} ($bsPlayer->id)");

            if(!$bsPlayer->active) {
                // Player is inactive, make sure it is not present in our DB
                Player::query()->where('broadsign_player_id', '=', $bsPlayer->id)->delete();
                continue;
            }

            $location = Location::query()->where("broadsign_display_unit",
                "=", $bsPlayer->display_unit_id)->first(["id"]);

            if($location === null) {
                // Ignore player
                Log::warning("Could not find display unit {$bsPlayer->display_unit_id} for player {$bsPlayer->name} ($bsPlayer->id). Ignoring...");
                continue;
            }

            /** @var Player $player */
            $player = Player::query()->firstOrCreate([
                "broadsign_player_id" => $bsPlayer->id,
            ], [
                "location_id" => $location->id,
                "name"        => $bsPlayer->name,
            ]);

            $players[] = $player->id;
        }

        $progressBar->setMessage("Players syncing done!");
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");

        // Erase missing players
        Player::whereNotIn("id", $players)->delete();
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
