<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherReport.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class WeatherReport extends Data {
	public function __construct(
		public CurrentWeatherDatum $now,

		#[DataCollectionOf(HourWeatherDatum::class)]
		public DataCollection      $forecast_hourly,

		#[DataCollectionOf(DayWeatherDatum::class)]
		public DataCollection      $forecast_daily,
	) {
	}
}
