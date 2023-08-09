<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenshotsController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Error;
use Exception;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\Screenshots\DestroyScreenshotsRequest;
use Neo\Http\Requests\Screenshots\ListScreenshotsRequest;
use Neo\Models\ContractFlight;
use Neo\Models\Screenshot;
use Neo\Models\ScreenshotRequest;

class ScreenshotsController extends Controller {
	public function index(ListScreenshotsRequest $request) {
		$flight = ContractFlight::query()->find($request->input("flight_id"));


		$allScreenshots = DB::select(<<<EOL
            WITH `flight_players` AS (
              SELECT `players`.`id` AS `player_id`, `l`.`id` AS `location_id`, `cl`.`product_id` AS `product_id`
              FROM `players`
              JOIN `locations` `l` ON `players`.`location_id` = `l`.`id`
              JOIN `products_locations` `pl` ON `l`.`id` = `pl`.`location_id`
              JOIN `contracts_lines` `cl` ON `pl`.`product_id` = `cl`.`product_id`
              WHERE `cl`.`flight_id` = ?
            )
            SELECT DISTINCT `screenshots`.*
            FROM `screenshots`
            WHERE (`screenshots`.`product_id` IN (SELECT `product_id` FROM `flight_players`)
            OR `screenshots`.`location_id` IN (SELECT `location_id` FROM `flight_players`)
            OR `screenshots`.`player_id` IN (SELECT `player_id` FROM `flight_players`))
            AND `screenshots`.`received_at` BETWEEN ? AND ?
            ORDER BY `received_at` DESC
            EOL,
			[$flight->getKey(), $flight->start_date, $flight->end_date]);

		$totalCount = count($allScreenshots);

		$page  = $request->input("page", 1);
		$count = $request->input("count", 250);
		$from  = ($page - 1) * $count;
		$to    = ($page * $count) - 1;

		$screenshots = Screenshot::hydrate(array_slice($allScreenshots, $from, $count));

		return new Response($screenshots->loadPublicRelations(), 200, [
			"Content-Range" => "items $from-$to/$totalCount",
		]);
	}

	/**
	 * @throws Exception
	 */
	public function receive(Request $request, ScreenshotRequest $screenshotRequest): void {
		$screenshot              = new Screenshot();
		$screenshot->request_id  = $screenshotRequest->id;
		$screenshot->product_id  = $screenshotRequest->product_id;
		$screenshot->location_id = $screenshotRequest->location_id;
		$screenshot->player_id   = $screenshotRequest->player_id;
		$screenshot->received_at = Carbon::now();
		$screenshot->save();

		$tries     = 0;
		$succeeded = false;
		do {
			try {
				$screenshot->store($request->getContent(true));
				$succeeded = true;
			} catch (ServerException $e) {
				if ($e->getCode() === 503) {
					$tries++;
					usleep(random_int(0, 1_000_000));
				}
			}
		} while ($tries < 5 && !$succeeded);

		if (!$succeeded) {
			throw new Error("Could not reliably communicate with CDN.");
		}
	}

	public function destroy(DestroyScreenshotsRequest $request, Screenshot $screenshot) {
		$screenshot->delete();

		return new Response([]);
	}
}
