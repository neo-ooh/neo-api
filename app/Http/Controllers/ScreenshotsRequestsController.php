<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenshotsRequestsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\MissingRequestTargeting;
use Neo\Http\Requests\ScreenshotsRequests\DeleteScreenshotRequestRequest;
use Neo\Http\Requests\ScreenshotsRequests\ListScreenshotRequestRequest;
use Neo\Http\Requests\ScreenshotsRequests\StoreScreenshotRequestRequest;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\ScreenshotRequest;

class ScreenshotsRequestsController extends Controller {
	public function index(ListScreenshotRequestRequest $request) {
		/** @var ContractFlight $flight */
		$flight = ContractFlight::query()->find($request->input("flight_id"));

		$requests = ScreenshotRequest::query()->fromQuery(<<<EOL
            WITH `flight_players` AS (
              SELECT `players`.`id` AS `player_id`, `l`.`id` AS `location_id`, `cl`.`product_id` AS `product_id`
              FROM `players`
              JOIN `locations` `l` ON `players`.`location_id` = `l`.`id`
              JOIN `products_locations` `pl` ON `l`.`id` = `pl`.`location_id`
              JOIN `contracts_lines` `cl` ON `pl`.`product_id` = `cl`.`product_id`
              WHERE `cl`.`flight_id` = ?
            )
            SELECT DISTINCT `screenshots_requests`.*
            FROM `screenshots_requests`
            WHERE (`screenshots_requests`.`product_id` IN (SELECT `product_id` FROM `flight_players`)
            OR `screenshots_requests`.`location_id` IN (SELECT `location_id` FROM `flight_players`)
            OR `screenshots_requests`.`player_id` IN (SELECT `player_id` FROM `flight_players`))
            AND `screenshots_requests`.`send_at` BETWEEN ? AND ?
            ORDER BY `screenshots_requests`.`send_at` DESC
            EOL,
			[$flight->getKey(), $flight->start_date, $flight->end_date->addDay()]);

		return new Response($requests->loadPublicRelations());
	}

	/**
	 * @throws MissingRequestTargeting
	 */
	public function store(StoreScreenshotRequestRequest $request): Response {
		$productId  = $request->input("product_id");
		$locationId = $request->input("location_id");
		$playerId   = $request->input("player_id");

		if (!$productId && !$locationId && !$playerId) {
			throw new MissingRequestTargeting();
		}

		$sendAt       = $request->input("send_at");
		$scalePercent = $request->input("scale_percent");
		$durationMs   = $request->input("duration_ms");
		$frequencyMs  = $request->input("frequency_ms");

		// If the user is not allowed to select the screenshotRequest quality, we make it is set to the default value
		if (!Gate::allows(Capability::screenshots_requests_quality->value)) {
			$scalePercent = config("modules-legacy.broadsign.bursts.default-quality");
		}

		$screenshotRequest              = new ScreenshotRequest();
		$screenshotRequest->product_id  = $request->input("product_id");
		$screenshotRequest->location_id = $request->input("location_id");
		$screenshotRequest->player_id   = $request->input("player_id");

		$screenshotRequest->send_at       = $sendAt;
		$screenshotRequest->sent          = false;
		$screenshotRequest->scale_percent = $scalePercent;
		$screenshotRequest->duration_ms   = $durationMs;
		$screenshotRequest->frequency_ms  = $frequencyMs;

		$screenshotRequest->save();

		return new Response($screenshotRequest, 201);
	}

	public function show(ScreenshotRequest $screenshotRequest): Response {
		return new Response($screenshotRequest->load('screenshots'));
	}

	public function destroy(DeleteScreenshotRequestRequest $request, ScreenshotRequest $screenshotRequest): Response {
		if (!$screenshotRequest->sent) {
			$screenshotRequest->delete();
		}

		return new Response(["status" => "ok"]);
	}
}
