<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\WeatherLocations\ListWeatherLocationsRequest;
use Neo\Http\Requests\WeatherLocations\ShowWeatherLocationRequest;
use Neo\Models\WeatherLocation;

class WeatherLocationsController extends Controller {
    public function index(ListWeatherLocationsRequest $request) {
        return new Response(WeatherLocation::query()->orderBy("country")->orderBy("province")->orderBy("city")->get());
    }

    public function show(ShowWeatherLocationRequest $request, string $country, string $province, string $city) {
        return new Response(WeatherLocation::fromComponents($country, $province, $city));
    }
}
