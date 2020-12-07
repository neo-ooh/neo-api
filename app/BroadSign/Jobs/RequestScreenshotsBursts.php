<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - RequestScreenshotsBursts.php
 */

namespace Neo\BroadSign\Jobs;


use Carbon\Carbon as Date;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Neo\BroadSign\Models\Player as BSPlayer;
use Neo\Models\Burst;
use Neo\Models\Player;


class RequestScreenshotsBursts implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Load bursts starting now or up to one minute in the future
        /** @var Collection $bursts */
        $bursts = Burst::query()->where("started", "=", false)
                       ->whereDate("start_at", "<=", Date::now()->addMinute())
                       ->distinct()
                       ->get();

        $bursts->each(fn($burst) => $this->sendRequest($burst));
    }

    /**
     * @param Burst $burst
     * @throws JsonException
     */
    protected function sendRequest(Burst $burst) {
        // Get one random player for the location of the burst
        /** @var Player|null $player */
        $player = $burst->location->players()->inRandomOrder()->first();

        if(is_null($player)) {
            // This location has no player, delete the burst
            $burst->delete();
            return;
        }

        $bsPlayer = new BSPlayer(["id" => $player->broadsign_player_id]);
        $bsPlayer->requestScreenshotsBurst($burst->id, $burst->scale_factor, $burst->duration_ms, $burst->frequency_ms);

        // Update the start date to reflect the effective start date.
        $burst->start_at = Date::now();
        $burst->started = true;
        $burst->save();
    }
}
