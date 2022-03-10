<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CountriesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Countries\ListCountriesRequest;
use Neo\Http\Requests\Countries\ShowCountryRequest;
use Neo\Models\Country;

class CountriesController extends Controller {
    public function index(ListCountriesRequest $request) {
        return new Response(Country::query()->orderBy("name")->with("provinces", "provinces.markets")->get());
    }

    public function show(ShowCountryRequest $request, Country $country) {
        return new Response($country->load("provinces"));
    }
}
