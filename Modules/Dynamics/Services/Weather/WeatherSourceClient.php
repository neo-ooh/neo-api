<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherSourceClient.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use Neo\Modules\Dynamics\Exceptions\CouldNotFetchThirdPartyDataException;
use RuntimeException;

class WeatherSourceClient implements WeatherAdapter {

    protected array $fields = [
        "all",
        "allSummary",
    ];

    /**
     * @throws GuzzleException
     * @throws JsonException
     * @throws CouldNotFetchThirdPartyDataException
     */
    public function getWeather(float $lng, float $lat, string $locale): WeatherReport {
        $geoValues = "$lat,$lng";
        $client    = new Client();

//		$responses = Utils::settle([
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//		                           ])->wait();

        // Current weather
        try {
            $rawReport = $client->get("https://appwx.weathersourceapis.com/v2/points/$geoValues", [
                "headers" => [
                    "X-API-KEY" => config('dynamics.weather.api-key'),
                ],
                "query"   => [
                    "services"  => "all",
                    "fields"    => implode(",", $this->fields),
                    "unitScale" => "METRIC",
                    "language"  => $locale === 'fr' ? "FRENCH" : "ENGLISH",
                ],
            ]);
        } catch (RequestException $e) {
            throw new CouldNotFetchThirdPartyDataException($e->getResponse());
        }

        if ($rawReport->getStatusCode() !== 200) {
            throw new CouldNotFetchThirdPartyDataException($rawReport);
        }

        $rawReportBody = json_decode($rawReport->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if ($rawReportBody["nowcast"]["temp"] === null) {
            throw new RuntimeException("Invalid Weather Report format: " . json_encode($rawReportBody, JSON_THROW_ON_ERROR));
        }

        // Build a weather report with the received responses
        return new WeatherReport(
            now            : $this->makeWeatherDatum($rawReportBody["nowcast"]),
            forecast_hourly: HourWeatherDatum::collection(collect($rawReportBody["forecastHour"])
                                                              ->map(fn(array $values) => $this->makeWeatherForecastHour($values))),

            forecast_daily : DayWeatherDatum::collection(collect($rawReportBody["forecastDay"])
                                                             ->map(fn(array $values) => $this->makeWeatherForecastDay($values))),
        );
    }

    public function makeWeatherDatum(array $values) {
        $date = Carbon::parse($values["timestamp"], "utc");
        return new CurrentWeatherDatum(
            date                   : $date->toDateString(),
            time                   : $date->toTimeString(),
            units                  : "metric",
            temperature            : $values["temp"],
            feels_like             : $values["feelsLike"],
            condition              : $this->mapIconToCondition($values["icon"]),
            condition_string       : $values["summary"],
            cloud_coverage_percent : $values["cldCvr"],
            atmospheric_pressure   : 0, //$values["sfcPres"],
            humidity_percent       : $values["relHum"],
            is_raining             : (bool)$values["precipFlag"],
            rain_intensity_percent : $values["precipIntensity"],
            is_snowing             : (bool)$values["snowfallFlag"],
            snow_intensity_percent : $values["snowfallIntensity"],
            wind_direction_cardinal: $values["windDir"],
            wind_speed             : $values["windSpd"]
        );
    }

    public function makeWeatherForecastHour(array $values) {
        $date = Carbon::parse($values["timestamp"]);
        return new HourWeatherDatum(
            date                    : $date->toDateString(),
            time                    : $date->toTimeString(),
            units                   : "metric",
            temperature             : $values["temp"],
            feels_like              : $values["feelsLike"],
            condition               : $this->mapIconToCondition($values["icon"]),
            condition_string        : $values["summary"],
            cloud_coverage_percent  : $values["cldCvr"],
            atmospheric_pressure    : 0, //$values["sfcPres"],
            humidity_percent        : $values["relHum"],
            rain_probability_percent: $values["precipProb"],
            snow_probability_percent: $values["snowfallProb"],
            wind_direction_cardinal : (int)$values["windDir"],
            wind_speed              : $values["windSpd"]
        );
    }

    public function makeWeatherForecastDay(array $values) {
        return new DayWeatherDatum(
            date                    : $values["date"],
            units                   : "metric",
            temperature_min         : $values["tempMin"],
            temperature_max         : $values["tempMax"],
            feels_like_min          : $values["feelsLikeMin"],
            feels_like_max          : $values["feelsLikeMax"],
            condition               : $this->mapIconToCondition($values["icon"]),
            condition_string        : $values["summary"],
            cloud_coverage_percent  : ($values["cldCvrMin"] + $values["cldCvrMax"]) / 2,
            atmospheric_pressure    : 0, //$values["sfcPres"],
            humidity_percent        : ($values["relHumMin"] + $values["relHumMax"]) / 2,
            rain_probability_percent: $values["precipProb"],
            snow_probability_percent: $values["snowfallProb"],
            wind_direction_cardinal : $values["windDirAvg"],
            wind_speed              : ($values["windSpdMin"] + $values["windSpdMax"]) / 2,
        );
    }

    protected function mapIconToCondition(string $icon): WeatherCondition {
        $tokens        = explode("-", $icon);
        $ignoredTokens = ["wi", "night", "alt", "day"];

        do {
            array_shift($tokens);
        } while (in_array($tokens[0], $ignoredTokens));

        return match (implode('-', $tokens)) {
            "cloudy", "cloudy-gusts", "cloudy-high", "cloudy-windy", "sunny-overcast"                 => WeatherCondition::Cloudy,
            "fog", "haze"                                                                             => WeatherCondition::Fog,
            "showers", "hail"                                                                         => WeatherCondition::HeavyRain,
            "partly-cloudy", "strong-wind"                                                            => WeatherCondition::MostlySunny,
            "sprinkle"                                                                                => WeatherCondition::LightRain,
            "rain-mix"                                                                                => WeatherCondition::RainAndSun,
            "rain", "rain-wind", "sleet", "sleet-storm"                                               => WeatherCondition::Rain,
            "snow", "snow-wind"                                                                       => WeatherCondition::Snow,
            "clear", "hot", "lunar-eclipse", "light-wind", "solar-eclipse", "sunny", "stars", "windy" => WeatherCondition::Sunny,
            "lightning", "thunderstorms", "storm-showers", "snow-thunderstorm"                        => WeatherCondition::Thunderstorm,
            default                                                                                   => throw new RuntimeException("Unsupported weather icon for condition: $icon"),
        };
    }
}
