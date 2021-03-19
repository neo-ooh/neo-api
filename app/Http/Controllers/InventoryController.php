<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - InventoryController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Enums\Network;
use Neo\Http\Requests\Inventory\ShowInventoryRequest;
use Neo\Models\Actor;
use Neo\Models\Format;
use Neo\Models\Location;
use Neo\Models\Param;

class InventoryController extends Controller {
    public function index(ShowInventoryRequest $request) {
        $year    = $request->validated()["year"];
        $network = $request->validated()["network"];

        // Load all the display types matching the formats specified in the request
        $displayTypes = Format::query()
                              ->with("display_types")
                              ->findMany($request->validated()["formats"])
                              ->pluck("display_types")
                              ->flatten()
                              ->pluck("id")
                              ->unique()
                              ->values();

        if ($request->has("location_id")) {
            $locations = Location::where("id", "=", $request->validated()["location_id"])->get();
        } else {
            $locations = Actor::find(Param::find(Network::coerce($network)->value)->value)->getLocations(true, false, true, true);

            if ($request->has("province")) {
                $province  = $request->validated()["province"];
                $locations = $locations->filter(fn($location) => $location->province === $province);
            }

            if ($request->has("city")) {
                $city      = $request->validated()["city"];
                $locations = $locations->filter(fn($location) => $location->city === $city);
            }
        }

        // Filter locations by their display type
        $locations = $locations->filter(/**
         * @param Location $location
         * @return bool
         */ fn($location) => $displayTypes->contains($location->display_type_id));

        // Load the format of each location
        /** @var Location $location */
        foreach ($locations as $location) {
            $location->format = $location->display_type->formats()
                                                       ->without(["layouts", "display_types"])
                                                       ->first()
                                                       ->only("id", "name");
        }

        // Load the inventory of each location
        $locations->load(["inventory" => function ($query) {
            $query->where("year", "=", 2021);
        }]);

        return new Response($locations->sortBy("name")->values());
    }
}
