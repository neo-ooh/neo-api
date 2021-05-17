<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\InvalidLocationException;
use Neo\Http\Requests\Hourly\ForecastWeatherRequest;
use Neo\Http\Requests\Weather\CurrentWeatherRequest;
use Neo\Http\Requests\Weather\HourlyWeatherRequest;
use Neo\Http\Requests\Weather\NationalWeatherRequest;
use Neo\Http\Requests\Weather\NextDayWeatherRequest;
use Neo\Services\Weather\Location;
use Neo\Services\Weather\WeatherService;

class WeatherController extends Controller {
    public $nationalLocations = [
        ["CA", "ON", "Toronto"],
        ["CA", "ON", "Ottawa"],
        ["CA", "QC", "Montreal"],
        ["CA", "QC", "Quebec"],
        ["CA", "NS", "Halifax"],
        ["CA", "BC", "Victoria"],
        ["CA", "BC", "Vancouver"],
        ["CA", "AB", "Calgary"],
        ["CA", "AB", "Edmonton"],
        ["CA", "MB", "Winnipeg"],
    ];

    /**
     * Gives the national weather
     *
     * @param NationalWeatherRequest $request
     * @param WeatherService         $weather
     * @return Response
     */
    public function national(NationalWeatherRequest $request, WeatherService $weather): Response {
        $locale    = $request->input('locale');
        $forecasts = [];

        try {
            foreach ($this->nationalLocations as $location) {
                $forecasts[] = $weather->getCurrentWeather(new Location(...$location), $locale);
            }
        } catch (InvalidLocationException $e) {
            return new Response(null);
        }

        return new Response($forecasts);
    }

    /**
     * Give the current weather for the specified city
     *
     * @param CurrentWeatherRequest $request
     * @param WeatherService        $weather
     * @return Response
     */
    public function current(CurrentWeatherRequest $request, WeatherService $weather): Response {
        $location = new Location($request->input("country"), $request->input("province"), $request->input("city"));
        $locale   = $request->input('locale');

        try {
            $now      = $weather->getCurrentWeather($location, $locale);
            $longTerm = $weather->getForecastWeather($location, $locale);
        } catch (InvalidLocationException $e) {
            return new Response(null);
        }

        $forecast = array_merge($longTerm["LongTermPeriod"][0], $now);

        return new Response($forecast);
    }

    /**
     * Give the next day weather for the specified location
     *
     * @param NextDayWeatherRequest $request The request
     * @param WeatherService        $weather
     * @return Response
     */
    public function nextDay(NextDayWeatherRequest $request, WeatherService $weather): Response {
        $location = new Location($request->input("country"), $request->input("province"), $request->input("city"));
        $locale   = $request->input('locale');

        try {
            $longTerm = $weather->getForecastWeather($location, $locale);
        } catch (InvalidLocationException $e) {
            return new Response(null);
        }

        $forecast             = $longTerm["LongTermPeriod"][1];
        $forecast["Location"] = $longTerm["Location"];

        return new Response($forecast);
    }

    /**
     * Give the seven days weather for the specified location
     *
     * @param ForecastWeatherRequest $request The request
     * @param WeatherService         $weather
     * @return Response
     */
    public function forecast(ForecastWeatherRequest $request, WeatherService $weather): Response {
        $location = new Location($request->input("country"), $request->input("province"), $request->input("city"));
        $locale   = $request->input('locale');

        try {
            $forecast = $weather->getForecastWeather($location, $locale);
        } catch (InvalidLocationException $e) {
            return new Response(null);
        }

        array_splice($forecast["LongTermPeriod"], 0, 1);

        return new Response($forecast);
    }

    /**
     * Give the next hours weather forecast for the specified location
     *
     * @param HourlyWeatherRequest $request The request
     * @param WeatherService       $weather
     * @return Response
     */
    public function hourly(HourlyWeatherRequest $request, WeatherService $weather): Response {
        $location = new Location($request->input("country"), $request->input("province"), $request->input("city"));
        $locale   = $request->input('locale');

        try {
            $hourly = $weather->getHourlyWeather($location, $locale);
        } catch (InvalidLocationException $e) {
            return new Response(null);
        }

        return new Response($hourly);
    }
}
