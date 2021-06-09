<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\InvalidLocationException;
use Neo\Http\Requests\WeatherLocations\ListWeatherLocationsRequest;
use Neo\Http\Requests\WeatherLocations\ShowWeatherLocationRequest;
use Neo\Http\Requests\WeatherLocations\UpdateWeatherLocationRequest;
use Neo\Models\WeatherLocation;

class WeatherLocationsController extends Controller {
    public function index(ListWeatherLocationsRequest $request): Response {
        return new Response(WeatherLocation::query()
                                           ->orderBy("country")
                                           ->orderBy("province")
                                           ->orderBy("city")
                                           ->get());
    }

    /**
     * @throws InvalidLocationException
     */
    public function show(ShowWeatherLocationRequest $request, string $country, string $province, string $city): Response {
        return new Response(WeatherLocation::fromComponents($country, $province, $city, true));
    }

    public function update(UpdateWeatherLocationRequest $request, WeatherLocation $weatherLocation): Response {
        $selection                              = $request->input("background_selection");
        $weatherLocation->background_selection  = $selection;
        $weatherLocation->selection_revert_date = $selection === 'RANDOM' ? $request->input("selection_revert_date") : null;
        $weatherLocation->save();

        return new Response($weatherLocation);
    }
}
