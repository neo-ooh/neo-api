<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullWeatherDataJob.php
 */

namespace Neo\Modules\Dynamics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Neo\Jobs\PullCityGeolocationJob;
use Neo\Models\City;
use Neo\Modules\Dynamics\Services\Weather\WeatherAdapter;
use RuntimeException;

class PullWeatherDataJob implements ShouldQueue, ShouldBeUnique {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function uniqueId() {
		return $this->city->getKey();
	}

	public function __construct(public City $city) {
	}

	public function handle(WeatherAdapter $weatherAdapter): void {
		$cache     = Cache::store("dynamics")->tags(["weather"]);
		$reportKey = "weather-city-{$this->city->getKey()}";

        if($this->city->geolocation === null) {
            $cityCoordinatesFetcher = new PullCityGeolocationJob($this->city->getKey());
            $cityCoordinatesFetcher->handle();

            $this->city->refresh();

            if($this->city->geolocation === null) {
                throw new RuntimeException("Missing coordinates for {$this->city->name}(#{$this->city->id})");
            }
        }

		$weatherReport = $weatherAdapter->getWeather($this->city->geolocation->getCoordinates()[0],
		                                             $this->city->geolocation->getCoordinates()[1],
		                                             $this->city->province->slug === 'QC' ? 'fr' : 'en');
		$cache->forever($reportKey, $weatherReport);
	}
}
