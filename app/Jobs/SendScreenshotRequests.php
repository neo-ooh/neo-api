<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendScreenshotRequests.php
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
use Neo\Modules\Properties\Models\ScreenshotRequest;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScreenshotsBurst;

/**
 * Class SendScreenshotRequests
 *
 * @package Neo\BroadSign\Jobs\Players
 *
 * Screenshots requests are made asynchronously and batched every minute for performances.
 */
class SendScreenshotRequests implements ShouldBeUnique {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @throws Exception
	 */
	public function handle(): void {
		// Load screenshots requests starting now or up to one minute in the future
		/** @var Collection $requests */
		$requests = ScreenshotRequest::query()
		                             ->where("sent", "=", false)
		                             ->whereDate("send_at", "<=", Date::now()->setTimezone('America/Toronto')->addMinute())
		                             ->distinct()
		                             ->get();

		$requests->each(fn($request) => $this->sendRequest($request));
	}

	/**
	 * @param ScreenshotRequest $request
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function sendRequest(ScreenshotRequest $request): void {
		// Depending on how the request has been made, we may need to precises where it is going to be sent
		if (!$request->player_id) {
			if (!$request->location_id) {
				// Only product id is provided, select a random location of the product
				$location = $request->product?->locations()->inRandomOrder()->first();

				if (!$location) {
					// no location could be found, this is not supposed to happen. stop here
					$request->delete();
					return;
				}

				$request->location_id = $location->getKey();
			}

			// Select a player at random on the location
			$player = $request->location->players()->inRandomOrder()->first();

			if (!$player) {
				// No player could be found. Without a player we cannot send the request.
				$request->delete();
				return;
			}

			$request->player_id = $player->getKey();

			$request->save();
		}

		/** @var Player $player */
		$player = $request->player;

		/** @var BroadcasterOperator&BroadcasterScreenshotsBurst $broadcaster */
		$broadcaster = BroadcasterAdapterFactory::makeForNetwork($request->location->network_id);

		// Make sure the broadcaster support Screenshots burst, otherwise ignore location and delete burst
		if (!$broadcaster->hasCapability(BroadcasterCapability::ScreenshotsBurst)) {
			$request->delete();
			return;
		}

		$broadcaster->requestScreenshotsBurst(
			players     : [$player->toExternalBroadcastIdResource()],
			responseUri : config("app.url") . "/v1/third-parties/screenshots-requests/" . $request->getKey() . "/_receive",
			scale       : $request->scale_percent,
			duration_ms : $request->duration_ms,
			frequency_ms: $request->frequency_ms,
		);

		// Update the start date to reflect the effective start date.
		$request->send_at = Carbon::now()->setTimezone("America/Toronto")->shiftTimezone('UTC');
		$request->sent    = true;
		$request->save();
	}
}
