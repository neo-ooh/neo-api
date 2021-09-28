<?php

namespace Neo\Http\Controllers;

use Auth;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Neo\Enums\Capability;
use Neo\Http\Requests\PropertiesTraffic\ListTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\StoreTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\UpdatePropertyTrafficSettingsRequest;
use Neo\Models\Property;
use Neo\Models\PropertyTraffic;

class PropertiesTrafficController extends Controller {
    public function index(ListTrafficRequest $request, Property $property): Response {

        $yearTraffic = $property->traffic->data()
                                         ->orderBy("month")
                                         ->get()
                                         ->groupBy("year");

        return new Response($yearTraffic);
    }

    public function store(StoreTrafficRequest $request, Property $property): Response {
        $values = ["traffic" => $request->input("traffic")];

        if(Gate::allows(Capability::properties_edit) && $request->has("temporary")) {
            $values["temporary"] = $request->input("temporary");

            if($values["traffic"] === null && $values["temporary"] === null) {
                // remove the record instead of adding it
                PropertyTraffic::query()->where([
                    "property_id" => $property->actor_id,
                    "year"        => $request->input("year"),
                    "month"       => $request->input("month"),
                ])->delete();

                return new Response([], 201);
            }
        }



        $traffic = PropertyTraffic::query()->updateOrCreate([
            "property_id" => $property->actor_id,
            "year"        => $request->input("year"),
            "month"       => $request->input("month"),
        ], $values);

        return new Response($traffic, 201);
    }

    public function update(UpdatePropertyTrafficSettingsRequest $request, Property $property): Response {
        $trafficSettings                         = $property->traffic;
        $trafficSettings->is_required            = $request->input("is_required");
        $trafficSettings->start_year             = $request->input("start_year");
        $trafficSettings->grace_override         = $request->input("grace_override");
        $trafficSettings->input_method           = $request->input("input_method");
        $trafficSettings->missing_value_strategy = $request->input("missing_value_strategy");
        $trafficSettings->placeholder_value      = $request->input("placeholder_value");

        $forcePull = $trafficSettings->getOriginal("input_method") === 'MANUAL' && $trafficSettings->input_method === 'LINKETT';

        $trafficSettings->save();

        if ($trafficSettings->input_method === 'LINKETT') {
            $trafficSettings->source()
                            ->attach($request->input("source_id"), [
                                "uid" => $request->input("venue_id")
                            ]);
        } else {
            $trafficSettings->source()->sync([]);
        }

        if ($forcePull) {
            Artisan::queue("property:pull-traffic $property->actor_id");
        }

        return new Response($trafficSettings);
    }

}
