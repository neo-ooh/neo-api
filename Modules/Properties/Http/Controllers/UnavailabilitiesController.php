<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnavailabilitiesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\Unavailabilities\DestroyUnavailabilityRequest;
use Neo\Modules\Properties\Http\Requests\Unavailabilities\ShowUnavailabilityRequest;
use Neo\Modules\Properties\Http\Requests\Unavailabilities\StoreUnavailabilityRequest;
use Neo\Modules\Properties\Http\Requests\Unavailabilities\UpdateUnavailabilityRequest;
use Neo\Modules\Properties\Models\Unavailability;

class UnavailabilitiesController extends Controller {
    public function store(StoreUnavailabilityRequest $request) {
        $unavailability             = new Unavailability();
        $unavailability->start_date = $request->input("start_date", null);
        $unavailability->end_date   = $request->input("end_date", null);
        $unavailability->save();

        if ($request->has("property_id")) {
            DB::table("properties_unavailabilities")
              ->insert([
                           "property_id"       => $request->input("property_id"),
                           "unavailability_id" => $unavailability->getKey(),
                       ]);
        }

        if ($request->has("product_id")) {
            DB::table("products_unavailabilities")
              ->insert([
                           "product_id"        => $request->input("product_id"),
                           "unavailability_id" => $unavailability->getKey(),
                       ]);
        }

        return new Response($unavailability);
    }

    public function show(ShowUnavailabilityRequest $request, Unavailability $unavailability) {
        return new Response($unavailability, 201);
    }

    public function update(UpdateUnavailabilityRequest $request, Unavailability $unavailability) {
        $unavailability->start_date = $request->input("start_date");
        $unavailability->end_date   = $request->input("end_date");
        $unavailability->save();

        return new Response($unavailability);
    }

    public function destroy(DestroyUnavailabilityRequest $request, Unavailability $unavailability) {
        $unavailability->delete();

        return new Response(["status" => "ok"]);
    }
}
