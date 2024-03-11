<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IdentificationController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Dynamics\Http\Requests\Identify\IdentifyPlayerRequest;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\ResolvedProduct;

class IdentificationController extends Controller {
	public function identify(IdentifyPlayerRequest $request) {
		// Start by finding the player using the given id and broadcaster type
		$player = Player::query()
		                ->where("external_id", "=", $request->input("player_id"))
		                ->whereHas("network", function (Builder $query) use ($request) {
			                $query->whereHas("broadcaster_connection", function (Builder $query) use ($request) {
				                // TODO: Remove temp.
				                $playerType = $request->input("player_type");
				                if ($playerType === 'browser') {
					                $playerType = 'broadsign';
				                }

				                $query->where("broadcaster", "=", $playerType);
			                });
		                })->with(["location"])
		                ->first();

		if (!$player) {
			return new Response([], 404);
		}

		// We know the player, now list all formats that match the player and the given resolution
		$playerFormat = Format::query()
		                      ->whereHas("display_types", function (Builder $query) use ($player) {
			                      $query->where("id", "=", $player->location->display_type_id);
		                      })
		                      ->whereHas("layouts", function (Builder $query) use ($request) {
			                      $query->whereHas("frames", null, "=", 1);
			                      $query->whereHas("frames", function (Builder $query) use ($request) {
				                      $query->whereRaw("FLOOR((`frames`.`width` / `frames`.`height`) * 100) = FLOOR((? / ?) * 100)", [
					                      $request->input("width"),
					                      $request->input("height"),
				                      ]);
			                      });
		                      })
		                      ->first();

		$product = $playerFormat ? ResolvedProduct::query()
		                                          ->where("is_bonus", "=", false)
		                                          ->whereHas("locations", function (Builder $query) use ($player) {
			                                          $query->whereHas("players", function (Builder $query) use ($player) {
				                                          $query->where("id", "=", $player->getKey());
			                                          });
		                                          })
		                                          ->whereIn("format_id", $playerFormat->pluck("id"))
		                                          ->first() : null;

		$format = $product ? $product->format : $playerFormat;

		/** @var Property|null $property */
		$property = Property::query()
		                    ->whereHas("actor", function (Builder $query) use ($player) {
			                    $query->whereHas("own_locations", function (Builder $query) use ($player) {
				                    $query->where("id", "=", $player->location_id);
			                    });
		                    })
		                    ->firstOrFail();

        $address = $property?->address()->first()?->load("city");

        if(!$address) {
            return new Response([], 404);
        }

		/*		$product = ResolvedProduct::query()
										  ->where("is_bonus", "=", false)
										  ->whereHas("locations", function (Builder $query) use ($player) {
											  $query->whereHas("players", function (Builder $query) use ($player) {
												  $query->where("id", "=", $player->getKey());
											  });
										  })
										  ->where("format_id", "=", $format?->getKey())
										  ->first();*/

//		$format->layouts->first(fn(Layout $layout) => $layout->frames->count() === 1 && $layout->frames[0]->width === )

		return new Response([
			                    "player"      => $player,
			                    "format"      => $format ? [
				                    ...$format->toArray(),
				                    "frame" => $format->layouts()->whereHas("frames", null, "=", 1)->first()->frames()->first(),
			                    ] : null,
			                    "property_id" => $property->getKey(),
			                    //			                    "product"  => $product,
			                    //			                    "property" => $property,
			                    "address"     => $address->load("city"),
		                    ]);
	}
}
