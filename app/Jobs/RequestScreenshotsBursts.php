<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RequestScreenshotsBursts.php
 */

namespace Neo\Jobs;


use Carbon\Carbon as Date;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Neo\Models\Contract;
use Neo\Models\ContractBurst;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\Player as BSPlayer;

/**
 * Class RequestScreenshotsBursts
 *
 * @package Neo\BroadSign\Jobs\Players
 *
 * Screenshots requests are made asynchronously an
 * d batched every minutes for performances.
 */
class RequestScreenshotsBursts implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @throws Exception
     */
    public function handle(): void {
        if (config("app.env") !== "production") {
            return;
        }

        // Load bursts starting now or up to one minute in the future
        /** @var Collection $bursts */
        $bursts = ContractBurst::query()->where("status", "=", "PENDING")
                               ->whereDate("start_at", "<=", Date::now()->setTimezone('America/Toronto')->addMinute())
                               ->distinct()
                               ->get();

        $bursts->each(fn($burst) => $this->sendRequest($burst));
    }

    /**
     * @param ContractBurst $burst
     */
    protected function sendRequest(ContractBurst $burst): void {
        // Get one random player for the location of the burst
        /** @var Player|null $player */
        $player = $burst->location->players()->inRandomOrder()->first();

        if (is_null($player)) {
            // This location has no player, delete the burst
            $burst->delete();
            return;
        }

        $config          = Contract::getConnectionConfig();
        $broadsignClient = new BroadsignClient($config);

        $bsPlayer = new BSPlayer($broadsignClient, ["id" => $player->external_id]);
        $bsPlayer->requestScreenshotsBurst($burst->id, $burst->scale_percent, $burst->duration_ms, $burst->frequency_ms);

        // Update the start date to reflect the effective start date.
        $burst->start_at = Carbon::now()->setTimezone("America/Toronto")->shiftTimezone('UTC');
        $burst->status   = "ACTIVE";
        $burst->save();
    }
}
