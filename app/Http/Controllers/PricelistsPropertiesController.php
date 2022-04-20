<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistsPropertiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PricelistProperties\ListPricelistPropertiesRequest;
use Neo\Http\Requests\PricelistProperties\SyncPropertiesRequest;
use Neo\Models\Pricelist;
use Neo\Models\Property;

class PricelistsPropertiesController {
    public function index(ListPricelistPropertiesRequest $request, Pricelist $pricelist) {
        $properties = Property::query()->where("pricelist_id", "=", $pricelist->getKey())->get();

        return new Response($properties);
    }

    public function sync(SyncPropertiesRequest $request, Pricelist $pricelist) {
        $propertyIds = $request->input("ids", []);
        $pricelist->properties()->whereNotIn("actor_id", $propertyIds)->update([
            "pricelist_id" => null
        ]);
        Property::query()->whereIn("actor_id", $propertyIds)->update([
            "pricelist_id" => $pricelist->getKey(),
        ]);

        return new Response(["status" => "ok"]);
    }
}
