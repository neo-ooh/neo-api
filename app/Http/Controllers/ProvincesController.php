<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Provinces\ListProvincesRequest;
use Neo\Models\Country;
use Neo\Models\Province;

class ProvincesController extends Controller {
    public function index(ListProvincesRequest $request, Country $country) {
        return new Response($country->provinces->load(["markets"]));
    }

    public function show(ListProvincesRequest $request, Country $country, Province $province) {
        return new Response($province->load(["markets", "cities"]));
    }
}
