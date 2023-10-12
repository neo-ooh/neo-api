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
use Neo\Modules\Dynamics\Exceptions\CouldNotFetchThirdPartyDataException;

class WeatherSourceClient implements WeatherAdapter {

	protected array $fields = [
		"popular",
		"relHum",
		"sfcPres",
	];

	protected array $forecastHourFields = [
		"popular",
		"allPrecip",
		"relHum",
		"sfcPres",
	];

	protected array $forecastDayFields = [
		"popular",
		"relHumAvg",
		"allPrecip",
		"allPres",
		"allTemp",
	];

	public function getWeather(float $lng, float $lat): WeatherReport {
		$geoValues = "$lat,$lng";
		$client    = new Client();

//		$responses = Utils::settle([
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//			                           $client->getAsync("https://nowcast.weathersourceapis.com/v2/points/$geoValues", $requestConfig),
//		                           ])->wait();

		// Current weather
		$currentWeather = $client->get("https://nowcast.weathersourceapis.com/v2/points/$geoValues", [
			"headers" => [
				"X-API-KEY" => config('dynamics.weather.api-key'),
			],
			"query"   => [
				"fields"    => implode(",", $this->fields),
				"unitScale" => "METRIC",
			],
		]);
		if ($currentWeather->getStatusCode() !== 200) {
			throw new CouldNotFetchThirdPartyDataException($currentWeather);
		}

		// Hourly forecast
		sleep(1);
		$forecastHourlyWeather = $client->get("https://forecast.weathersourceapis.com/v2/points/$geoValues/hours", [
			"headers" => [
				"X-API-KEY" => config('dynamics.weather.api-key'),
			],
			"query"   => [
				"fields"    => implode(",", $this->forecastHourFields),
				"unitScale" => "METRIC",
			],
		]);
		if ($forecastHourlyWeather->getStatusCode() !== 200) {
			throw new CouldNotFetchThirdPartyDataException($forecastHourlyWeather);
		}


		// Daily forecast
		sleep(1);
		$forecastDailyWeather = $client->get("https://forecast.weathersourceapis.com/v2/points/$geoValues/days", [
			"headers" => [
				"X-API-KEY" => config('dynamics.weather.api-key'),
			],
			"query"   => [
				"fields"    => implode(",", $this->forecastDayFields),
				"unitScale" => "METRIC",
			],
		]);
		if ($forecastDailyWeather->getStatusCode() !== 200) {
			throw new CouldNotFetchThirdPartyDataException($forecastDailyWeather);
		}

		// Build a weather report with the received responses
		return new WeatherReport(
			now            : $this->makeWeatherDatum(json_decode($currentWeather->getBody()
			                                                                    ->getContents(), associative: true)["nowcast"]),
			forecast_hourly: WeatherForecastHour::collection(collect(json_decode($forecastHourlyWeather->getBody()
			                                                                                           ->getContents(), associative: true)["forecast"])
				                                                 ->map(fn(array $values) => $this->makeWeatherForecastHour($values))),

			forecast_daily : WeatherForecastDay::collection(collect(json_decode($forecastDailyWeather->getBody()
			                                                                                         ->getContents(), associative: true)["forecast"])
				                                                ->map(fn(array $values) => $this->makeWeatherForecastDay($values))),
		);
	}

	public function makeWeatherDatum(array $values) {
		$date = Carbon::parse($values["timestamp"], "utc");
		return new WeatherDatum(
			date                  : $date->toDateString(),
			time                  : $date->toTimeString(),
			units                 : "metric",
			temperature           : $values["temp"],
			feels_like            : $values["feelsLike"],
			cloud_coverage_percent: $values["cldCvr"],
			atmospheric_pressure  : $values["sfcPres"],
			humidity_percent      : $values["relHum"],
			is_raining            : (bool)$values["precipFlag"],
			rain_intensity_percent: $values["precipIntensity"],
			is_snowing            : (bool)$values["snowfallFlag"],
			snow_intensity_percent: $values["snowfallIntensity"],
			wind_direction_degrees: $values["windDir"],
			wind_speed            : $values["windSpd"]
		);
	}

	public function makeWeatherForecastHour(array $values) {
		$date = Carbon::parse($values["timestamp"]);
		return new WeatherForecastHour(
			date                    : $date->toDateString(),
			time                    : $date->toTimeString(),
			units                   : "metric",
			temperature             : $values["temp"],
			feels_like              : $values["feelsLike"],
			cloud_coverage_percent  : $values["cldCvr"],
			atmospheric_pressure    : $values["sfcPres"],
			humidity_percent        : $values["relHum"],
			rain_probability_percent: $values["precipProb"],
			snow_probability_percent: $values["snowfallProb"],
			wind_direction_degrees  : $values["windDir"],
			wind_speed              : $values["windSpd"]
		);
	}

	public function makeWeatherForecastDay(array $values) {
		return new WeatherForecastDay(
			date                    : $values["date"],
			units                   : "metric",
			temperature_min         : $values["tempMin"],
			temperature_max         : $values["tempMax"],
			feels_like_min          : $values["feelsLikeMin"],
			feels_like_max          : $values["feelsLikeMax"],
			cloud_coverage_percent  : $values["cldCvrAvg"],
			atmospheric_pressure    : $values["sfcPresMax"],
			humidity_percent        : $values["relHumAvg"],
			rain_probability_percent: $values["precipProb"],
			snow_probability_percent: $values["snowfallProb"],
			wind_direction_degrees  : $values["windDirAvg"],
			wind_speed              : $values["windSpdAvg"]
		);
	}
}
