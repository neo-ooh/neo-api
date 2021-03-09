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
use Neo\BroadSign\Models\Inventory;
use Neo\Http\Requests\Inventory\ShowInventoryRequest;
use Neo\Models\Location;

class InventoryController extends Controller {
    public function index(ShowInventoryRequest $request) {
        $year = $request->validated()["year"];
        // Start by loading all the locations
        $locationsIds = $request->validated()["locations"];
        $locations    = Location::query()->findMany($locationsIds);

        // For each location, we need to retrieve its frames and inventory for each one.
        // By frames, we mean here the skins associated with the display unit in BroadSign
        /** @var Location $location */
        foreach ($locations as $location) {
            $location->skins = Inventory::forDisplayUnit($location->broadsign_display_unit, $year);
        }

        return new Response($locations);
    }
}
