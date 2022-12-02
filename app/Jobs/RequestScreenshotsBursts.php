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
use Neo\Models\ContractBurst;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScreenshotsBurst;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

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
        /*        if (config("app.env") !== "production") {
                    return;
                }*/

        // Load bursts starting now or up to one minute in the future
        /** @var Collection $bursts */
        $bursts = ContractBurst::query()->where("status", "=", "PENDING")
                               ->where("id", "=", 6556)
                               ->whereDate("start_at", "<=", Date::now()->setTimezone('America/Toronto')->addMinute())
                               ->distinct()
                               ->get();

        $bursts->each(fn($burst) => $this->sendRequest($burst));
    }

    /**
     * @param ContractBurst $burst
     * @throws UnknownProperties
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

        /** @var BroadcasterOperator&BroadcasterScreenshotsBurst $broadcaster */
        $broadcaster = BroadcasterAdapterFactory::makeForNetwork($burst->location->network_id);

        // Make sure the broadcaster support Screenshots burst, otherwise ignore location and delete burst
        if (!$broadcaster->hasCapability(BroadcasterCapability::ScreenshotsBurst)) {
            $burst->delete();
            return;
        }

        $broadcaster->requestScreenshotsBurst(
            players: [$player->toExternalBroadcastIdResource()],
            responseUri: config("app.url") . "/v1/broadsign/burst_callback/" . $burst->getKey(),
            scale: $burst->scale_percent,
            duration_ms: $burst->duration_ms,
            frequency_ms: $burst->frequency_ms,
        );

        // Update the start date to reflect the effective start date.
        $burst->start_at = Carbon::now()->setTimezone("America/Toronto")->shiftTimezone('UTC');
        $burst->status   = "ACTIVE";
        $burst->save();
    }
}
