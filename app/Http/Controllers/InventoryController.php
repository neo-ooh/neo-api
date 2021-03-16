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
use Illuminate\Support\Facades\Log;
use Neo\BroadSign\Models\Skin;
use Neo\Http\Requests\Inventory\ShowInventoryRequest;
use Neo\Models\Actor;
use Neo\Models\Inventory;
use Neo\Models\Location;
use Neo\Models\Param;

class InventoryController extends Controller {
    public function index(ShowInventoryRequest $request) {
        $year    = $request->validated()["year"];
        $network = $request->validated()["network"];

        if ($request->has("location_id")) {
            $locations = Location::where("id", "=", $request->validated()["location_id"])->get();
        } else {
            $networks  = ["shopping" => "NETWORK_SHOPPING", "fitness" => "NETWORK_FITNESS", "otg" => "NETWORK_OTG"];
            $locations = Actor::find(Param::find($networks[$network])->value)->getLocations(true, false, true, true);

            if ($request->has("province")) {
                $province  = $request->validated()["province"];
                $locations = $locations->filter(fn($location) => $location->province === $province);
            }
        }

        // We want the inventory for each and every frame of all the selected locations
        /** @var Location $location */
        foreach ($locations as $location) {
            $skins = Skin::byDisplayUnit(["display_unit_id" => $location->broadsign_display_unit]);

            $skins->each(function ($skin) use ($year) {
                Log::debug($year);
                return $skin->inventory = Inventory::query()
                                                   ->where("skin_id", "=", $skin->id)
                                                   ->where("year", "=", $year)
                                                   ->first();
            });

            $location->skins = $skins;
        }

        return new Response($locations->values());
    }
}
