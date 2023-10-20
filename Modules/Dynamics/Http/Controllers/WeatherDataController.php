<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherDataController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Neo\Http\Controllers\Controller;
use Neo\Models\City;
use Neo\Modules\Dynamics\Http\Requests\WeatherData\ShowWeatherDataRequest;
use Neo\Modules\Dynamics\Jobs\PullWeatherDataJob;
use Neo\Modules\Dynamics\Services\Weather\WeatherAdapter;
use Neo\Modules\Dynamics\Services\Weather\WeatherReport;

class WeatherDataController extends Controller {
	public function show(ShowWeatherDataRequest $request, City $city, WeatherAdapter $weatherAdapter) {
		$cache     = Cache::store("dynamics")->tags(["weather"]);
		$reportKey = "weather-city-{$city->getKey()}";

		/** @var WeatherReport|null $weatherReport */
		$weatherReport = $cache->get($reportKey);

		// If no report could be found, request one in the background, and fail this request
		if (!$weatherReport) {
			PullWeatherDataJob::dispatch($city);
			return new Response(["error" => "Weather report not available. A request has been sent for it"], 500);
		}

		// If the weather report is too old, we trigger an update in the background and serve stale data
		$weatherReportAge = Carbon::parse($weatherReport->now->date . " " . $weatherReport->now->time)
		                          ->diffInMinutes(Carbon::now(), absolute: true);
		if ($weatherReportAge > config('dynamics.weather.reports-ttl-minutes')) {
			PullWeatherDataJob::dispatch($city);
		}

		return new Response($weatherReport, 200);
	}
}
