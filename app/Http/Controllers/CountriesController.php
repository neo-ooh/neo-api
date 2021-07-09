<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Countries\ListCountriesRequest;
use Neo\Http\Requests\Countries\ShowCountryRequest;
use Neo\Models\Country;

class CountriesController extends Controller {
    public function index(ListCountriesRequest $request) {
        return new Response(Country::query()->orderBy("name")->get());
    }

    public function show(ShowCountryRequest $request, Country $country) {
        return new Response($country->load("provinces"));
    }
}
