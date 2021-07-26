<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Neo\Http\Requests\WeatherBackgrounds\DestroyWeatherBackgroundRequest;
use Neo\Http\Requests\WeatherBackgrounds\ListWeatherBackgroundsRequest;
use Neo\Http\Requests\WeatherBackgrounds\StoreWeatherBackgroundRequest;
use Neo\Models\WeatherBackground;
use Neo\Models\WeatherLocation;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class WeatherBackgroundsController extends Controller {
    public function index(ListWeatherBackgroundsRequest $request) {
        $location = WeatherLocation::fromComponents($request->input("country"), $request->input("province"), $request->input("city"), true);

        // If the selection is set to Random, check its end_date for expiration.
        if ($location->background_selection === 'RANDOM' && $location->selection_revert_date->isPast()) {
            // Revert the selection to default (weather)
            $location->background_selection  = 'WEATHER';
            $location->selection_revert_date = null;
            $location->save();
        }

        // There is now two different collection strategy that can be applied here.
        // If the selection is random, we only return the backgrounds associated with this precise location. Otherwise, we the backgrounds of the location, plus any additional ones provided by the parents location, which is the location without the city set for a location with a city, and the location with just the country set. Only the backgrounds with the most 'precise' location are kept in case of duplicates by weather in the same period and format.

        if ($location->background_selection === 'RANDOM') {
            $backgrounds = $location->backgrounds()
                                    ->where("format_id", "=", $request->input("format_id"))
                                    ->where("period", "=", 'RANDOM')
                                    ->get();

            return new Response($backgrounds);
        }

        // Get the current location in an ordered array with its parents
        $locations = $this->getLocationAndParents($location);
        $backgrounds = collect();

        foreach ($locations as $location) {
            $backgrounds = $backgrounds->merge(
                WeatherBackground::query()->where("weather_location_id", "=", $location->id)
                ->where("format_id", "=", $request->input("format_id"))
                ->where("period", "=", $request->input("period"))
                ->whereNotIn("weather", $backgrounds->pluck("weather"))
                ->get()
            );
        }

        return new Response($backgrounds);
    }

    public function store(StoreWeatherBackgroundRequest $request) {
        $location = WeatherLocation::fromComponents($request->input("country"), $request->input("province"), $request->input("city"), true);

        if ($request->input("period") !== "RANDOM") {
            // Is there already a background for the specified properties ?
            $existingBackground = WeatherBackground::query()->where("weather_location_id", "=", $location->id)
                                                   ->where("period", "=", $request->input('period'))
                                                   ->where("weather", "=", $request->input('weather'))->first();

            if ($existingBackground) {
                $existingBackground->delete();
            }
        }

        // Validate the uploaded file before storing
        $file = $request->file("background");

        if (!$file->isValid()) {
            throw new UploadException("An error occurred while uploading the background");
        }

        $background                      = new WeatherBackground();
        $background->weather             = $request->input("weather");
        $background->period              = $request->input("period");
        $background->network_id              = $request->input("network_id");
        $background->weather_location_id = $location->id;
        $background->format_id           = $request->input("format_id");
        $background->path                = $file->storePubliclyAs(Storage::path("dynamics/weather/backgrounds/"), $file->hashName());
        $background->save();

        return new Response($background, 201);
    }

    public function destroy(DestroyWeatherBackgroundRequest $request, WeatherBackground $weatherBackground): Response {
        $weatherBackground->delete();

        return new Response(["status" => "ok"]);
    }

    protected function getLocationAndParents(WeatherLocation $location): array {
        $locations = [$location];

        if($location->city !== WeatherLocation::NULL_COMPONENT) {
            $locations[] = WeatherLocation::fromComponents($location->country, $location->province, WeatherLocation::NULL_COMPONENT, true);
        }

        if($location->province !== WeatherLocation::NULL_COMPONENT) {
            $locations[] = WeatherLocation::fromComponents($location->country, WeatherLocation::NULL_COMPONENT, WeatherLocation::NULL_COMPONENT, true);
        }

        return $locations;
    }
}
