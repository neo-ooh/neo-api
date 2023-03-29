<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Cities\DestroyCityRequest;
use Neo\Http\Requests\Cities\ListCitiesRequest;
use Neo\Http\Requests\Cities\StoreCityRequest;
use Neo\Jobs\PullCityGeolocationJob;
use Neo\Models\City;
use Neo\Models\Country;
use Neo\Models\Province;

class CitiesController extends Controller {
    public function store(StoreCityRequest $request, Country $country, Province $province): Response {
        $city              = new City();
        $city->province_id = $province->id;
        $city->market_id   = $request->input("market_id");
        $city->name        = $request->input("name");
        $city->save();

        PullCityGeolocationJob::dispatch($city->getKey());

        return new Response($city);
    }

    public function index(ListCitiesRequest $request, Country $country, Province $province): Response {
        return new Response(City::query()
                                ->where("province_id", "=", $province->id)
                                ->orderBy("name")
                                ->get());
    }

    public function update(DestroyCityRequest $request, Country $country, Province $province, City $city): Response {
        $city->name      = $request->input("name");
        $city->market_id = $request->input("market_id");
        $city->save();

        PullCityGeolocationJob::dispatch($city->getKey());

        return new Response($city);
    }

    public function destroy(DestroyCityRequest $request, Country $country, Province $province, City $city): Response {
        $city->delete();

        return new Response();
    }
}
