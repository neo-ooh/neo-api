<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PointsOfInterestController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Modules\Properties\Http\Requests\PointsOfInterest\DestroyPointOfInterestRequest;
use Neo\Modules\Properties\Http\Requests\PointsOfInterest\ListPointsOfInterestRequest;
use Neo\Modules\Properties\Http\Requests\PointsOfInterest\StoreBatchPointOfInterestRequest;
use Neo\Modules\Properties\Http\Requests\PointsOfInterest\StorePointOfInterestRequest;
use Neo\Modules\Properties\Http\Requests\PointsOfInterest\UpdatePointOfInterestRequest;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\PointOfInterest;

class PointsOfInterestController {
    public function index(ListPointsOfInterestRequest $request, Brand $brand) {
        $pois = $brand->pointsOfInterest()->get();

        return new Response($pois);
    }

    public function store(StorePointOfInterestRequest $request, Brand $brand) {
        // We need a POI model. If an external ID is provided. we check against stored pois.
        if ($request->has("external_id")) {
            /** @var PointOfInterest $poi */
            $poi = PointOfInterest::query()->where("external_id", "=", $request->input("external_id"))->firstOrNew();
        } else {
            $poi = new PointOfInterest();
        }

        $poi->name        = $request->input("name");
        $poi->address     = $request->input("address");
        $poi->brand_id    = $brand->getKey();
        $poi->external_id = $request->input("external_id");
        $poi->position    = new Point($request->input("position")["coordinates"][1], $request->input("position")["coordinates"][0]);

        $poi->save();

        return new Response($poi, 201);
    }

    /**
     * @param StoreBatchPointOfInterestRequest $request
     * @param Brand                            $brand
     * @return Response
     */
    public function storeBatch(StoreBatchPointOfInterestRequest $request, Brand $brand) {
        $inputs = collect($request->input("pois"));

        /** @var PointOfInterest $existingPois */
        $existingPois = PointOfInterest::query()
                                       ->whereIn("external_id", $inputs->pluck("external_id")->whereNotNull())
                                       ->get();
        $pois         = [];

        // For each input, we apply the same steps as the one we do for storing a single POI.
        foreach ($inputs as $input) {
            /** @var PointOfInterest|null $poi */
            $poi = null;

            if (array_key_exists("external_id", $input)) {
                $poi = $existingPois->where("external_id", "=", $input["external_id"])->first();
            }

            if (!$poi) {
                $poi = new PointOfInterest();
            }

            $poi->name        = $input["name"];
            $poi->address     = $input["address"];
            $poi->brand_id    = $brand->getKey();
            $poi->external_id = $input["external_id"];
            $poi->position    = new Point($input["position"]["coordinates"][1], $input["position"]["coordinates"][0]);
            $poi->save();
            $poi->refresh();

            $pois[] = $poi;
        }

        return new Response($pois, 201);
    }

    public function update(UpdatePointOfInterestRequest $request, Brand $brand, PointOfInterest $poi) {
        $poi->name = $request->input("name");
        $poi->save();

        return new Response($poi);
    }

    public function destroy(DestroyPointOfInterestRequest $request, Brand $brand, PointOfInterest $poi) {
        $poi->delete();

        return new Response(["status" => "ok"]);
    }
}
