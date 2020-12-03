<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Neo\BroadSign\Models\Player as BSPlayer;
use Neo\Models\Burst;


class RequestScreenshotsBurst implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // ID of the associated burst request
    protected int $burstID;

    public function __construct (int $burstID) {
        $this->burstID = $burstID;
    }

    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Get the burst details
        /** @var Burst $burst */
        $burst = Burst::query()->findOrFail($this->burstID);

        // Get the player and send the request
        $bsPlayer = BSPlayer::get($burst->player->broadsign_player_id);
        $bsPlayer->requestScreenshotsBurst($burst->id, $burst->scale_factor, $burst->duration_ms, $burst->frequency_ms);

        // Update the start date to reflect the effective start date.
        $burst->started_at = Date::now();
        $burst->save();
    }
}
