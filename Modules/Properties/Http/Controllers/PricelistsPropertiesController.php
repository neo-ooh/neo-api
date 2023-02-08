<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistsPropertiesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\PricelistProperties\ListPricelistPropertiesRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProperties\SyncPropertiesRequest;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\Property;

class PricelistsPropertiesController {
    public function index(ListPricelistPropertiesRequest $request, Pricelist $pricelist) {
        $properties = Property::query()->where("pricelist_id", "=", $pricelist->getKey())->get();

        return new Response($properties);
    }

    public function sync(SyncPropertiesRequest $request, Pricelist $pricelist) {
        $propertyIds = $request->input("ids", []);
        $pricelist->properties()->whereNotIn("actor_id", $propertyIds)->update([
                                                                                   "pricelist_id" => null,
                                                                               ]);
        Property::query()->whereIn("actor_id", $propertyIds)->update([
                                                                         "pricelist_id" => $pricelist->getKey(),
                                                                     ]);

        return new Response(["status" => "ok"]);
    }
}
