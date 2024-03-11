<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesTrafficController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\PropertiesTraffic\ListTrafficRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTraffic\UpdatePropertyTrafficSettingsRequest;
use Neo\Modules\Properties\Models\Property;

class PropertiesTrafficController extends Controller {
    public function index(ListTrafficRequest $request, Property $property): Response {

        $yearTraffic = $property->traffic->data()
                                         ->orderBy("month")
                                         ->get()
                                         ->groupBy("year");

        return new Response($yearTraffic);
    }

    public function update(UpdatePropertyTrafficSettingsRequest $request, Property $property): Response {
        $trafficSettings                         = $property->traffic;
        $trafficSettings->format                 = $request->input("format");
        $trafficSettings->is_required            = $request->input("is_required");
        $trafficSettings->start_year             = $request->input("start_year");
        $trafficSettings->grace_override         = $request->input("grace_override");
        $trafficSettings->input_method           = $request->input("input_method");
        $trafficSettings->missing_value_strategy = $request->input("missing_value_strategy");
        $trafficSettings->placeholder_value      = $request->input("placeholder_value");

        $forcePull = $trafficSettings->getOriginal("input_method") === 'MANUAL' && $trafficSettings->input_method === 'LINKETT';

        $trafficSettings->save();

        $trafficSettings->source()->sync([]);

        if ($trafficSettings->input_method === 'LINKETT') {
            $trafficSettings->source()
                            ->attach($request->input("source_id"), [
                                "uid" => $request->input("venue_id"),
                            ]);
        }

        Cache::forget($trafficSettings->getRollingWeeklyTrafficCacheKey());

        if ($forcePull) {
            Artisan::queue("properties:pull-traffic $property->actor_id");
        }

        return new Response($trafficSettings->load(["source"]));
    }

}
