<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\WeatherLocations\ListWeatherLocationsRequest;
use Neo\Models\WeatherLocation;

class WeatherLocationsController extends Controller {
    public function index(ListWeatherLocationsRequest $request) {
        return new Response(WeatherLocation::query()->orderBy("country")->orderBy("province")->orderBy("city")->get());
    }
}
