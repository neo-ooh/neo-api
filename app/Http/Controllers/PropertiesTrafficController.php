<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PropertiesTraffic\ListTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\StoreTrafficRequest;
use Neo\Models\Property;
use Neo\Models\PropertyTraffic;

class PropertiesTrafficController extends Controller {
    public function index(ListTrafficRequest $request, Property $property) {

        $yearTraffic = $property->traffic_data()
                                ->where("year", "=", $request->input("year"))
                                ->orderBy("month")
                                ->get();

        return new Response($yearTraffic);
    }

    public function store(StoreTrafficRequest $request, Property $property) {
        $traffic = PropertyTraffic::query()->updateOrCreate([
            "property_id" => $property->actor_id,
            "year" => $request->input("year"),
            "month" => $request->input("month"),
        ], [
            "traffic" => $request->input("traffic"),
        ]);

        return new Response($traffic, 201);
    }

}
