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
use Neo\Modules\Properties\Models\UnavailabilityTranslation;

class UnavailabilitiesController extends Controller {
    public function store(StoreUnavailabilityRequest $request) {
        $unavailability             = new Unavailability();
        $unavailability->start_date = $request->input("start_date", null);
        $unavailability->end_date   = $request->input("end_date", null);
        $unavailability->save();

        if ($request->has("property")) {
            $unavailability->properties()->sync([$request->input("property")]);
        }

        if ($request->has("products")) {
            $unavailability->products()->sync($request->input("products"));
        }

        $translations = collect($request->input("translations"));

        DB::table((new UnavailabilityTranslation())->getTable())
          ->insert($translations->map(fn($translation) => [
              "unavailability_id" => $unavailability->getKey(),
              "locale"            => $translation["locale"],
              "reason"            => $translation["reason"],
              "comment"           => $translation["comment"] ?? "",
          ])->toArray());

        return new Response($unavailability);
    }

    public function show(ShowUnavailabilityRequest $request, Unavailability $unavailability) {
        return new Response($unavailability->loadPublicRelations(), 201);
    }

    public function update(UpdateUnavailabilityRequest $request, Unavailability $unavailability) {
        $unavailability->start_date = $request->input("start_date", null);
        $unavailability->end_date   = $request->input("end_date", null);
        $unavailability->save();

        $unavailability->properties()->sync($request->has("property") ? [$request->input("property")] : []);
        $unavailability->products()->sync($request->input("products", []));

        $translations = collect($request->input("translations"));

        UnavailabilityTranslation::query()->upsert(
              $translations->map(fn($translation) => [
                  "unavailability_id" => $unavailability->getKey(),
                  "locale"            => $translation["locale"],
                  "reason"            => $translation["reason"],
                  "comment"           => $translation["comment"] ?? "",
              ])->toArray()
            , ["unavailability_id", "locale"], ["reason", "comment"]
        );

        return new Response($unavailability);
    }

    public function destroy(DestroyUnavailabilityRequest $request, Unavailability $unavailability) {
        $unavailability->delete();

        return new Response(["status" => "ok"]);
    }
}
