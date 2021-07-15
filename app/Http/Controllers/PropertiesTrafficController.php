<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PropertiesTraffic\ListTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\StoreTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\UpdatePropertyTrafficSettingsRequest;
use Neo\Models\Property;
use Neo\Models\PropertyTraffic;

class PropertiesTrafficController extends Controller {
    public function index(ListTrafficRequest $request, Property $property) {

        $yearTraffic = $property->traffic->data()
                                         ->where("year", "=", $request->input("year"))
                                         ->orderBy("month")
                                         ->get();

        return new Response($yearTraffic);
    }

    public function store(StoreTrafficRequest $request, Property $property) {
        $traffic = PropertyTraffic::query()->updateOrCreate([
            "property_id" => $property->actor_id,
            "year"        => $request->input("year"),
            "month"       => $request->input("month"),
        ], [
            "traffic" => $request->input("traffic"),
        ]);

        return new Response($traffic, 201);
    }

    public function update(UpdatePropertyTrafficSettingsRequest $request, Property $property) {
        $trafficSettings                         = $property->traffic;
        $trafficSettings->is_required            = $request->input("is_required");
        $trafficSettings->start_year             = $request->input("start_year");
        $trafficSettings->grace_override         = $request->input("grace_override");
        $trafficSettings->input_method           = $request->input("input_method");
        $trafficSettings->missing_value_strategy = $request->input("missing_value_strategy");
        $trafficSettings->placeholder_value      = $request->input("placeholder_value");

        $trafficSettings->save();

        if($trafficSettings->input_method === 'LINKETT') {
            $trafficSettings->source()
                            ->attach($request->input("source_id"), [
                                "uid" => $request->input("venue_id")
                            ]);
        } else {
            $trafficSettings->source()->sync([]);
        }

        return new Response($trafficSettings);
    }

}
