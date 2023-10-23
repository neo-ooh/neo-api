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

class IdentificationController extends Controller {
	public function identify(IdentifyPlayerRequest $request) {
		// Start by finding the player using the given id and broadcaster type
		$player = Player::query()->where("external_id", "=", $request->input("player_id"))
		                ->whereHas("network", function (Builder $query) use ($request) {
			                $query->whereHas("broadcaster_connection", function (Builder $query) use ($request) {
				                $query->where("broadcaster", "=", $request->input("player_type"));
			                });
		                })->with(["location"])
		                ->first();

		if (!$player) {
			return new Response([], 404);
		}

		// We know the player, now find the format and product associated
		$format = Format::query()
		                ->whereHas("display_types", function (Builder $query) use ($player) {
			                $query->where("id", "=", $player->location->display_type_id);
		                })
		                ->whereHas("layouts", function (Builder $query) use ($request) {
			                $query->whereHas("frames", function (Builder $query) use ($request) {
				                $query->whereRaw("FLOOR((`frames`.`width` / `frames`.`height`) * 100) = FLOOR((? / ?) * 100)", [
					                $request->input("width"),
					                $request->input("height"),
				                ]);
			                });
		                })
		                ->first();

		/** @var Property|null $property */
		$property = Property::query()
		                    ->whereHas("actor", function (Builder $query) use ($player) {
			                    $query->whereHas("own_locations", function (Builder $query) use ($player) {
				                    $query->where("id", "=", $player->location_id);
			                    });
		                    })
		                    ->first();

		/*		$product = ResolvedProduct::query()
										  ->where("is_bonus", "=", false)
										  ->whereHas("locations", function (Builder $query) use ($player) {
											  $query->whereHas("players", function (Builder $query) use ($player) {
												  $query->where("id", "=", $player->getKey());
											  });
										  })
										  ->where("format_id", "=", $format?->getKey())
										  ->first();*/

		return new Response([
			                    "player"      => $player,
			                    "format"      => $format,
			                    "property_id" => $property?->getKey(),
			                    //			                    "product"  => $product,
			                    //			                    "property" => $property,
			                    "address"     => $property?->address()->first(),
		                    ]);
	}
}
