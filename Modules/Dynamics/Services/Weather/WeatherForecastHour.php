<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherForecastHour.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

use Spatie\LaravelData\Data;

class WeatherForecastHour extends Data {
	public function __construct(
		public string $date,

		public string $time,

		/**
		 * @var 'metric'|'imperial' Celsius/Fahrenheit
		 */
		public string $units,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float  $temperature,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float  $feels_like,

		/**
		 * @var float
		 */
		public float  $cloud_coverage_percent,

		/**
		 * @var float Millibars
		 */
		public float  $atmospheric_pressure,
		public float  $humidity_percent,

		public float  $rain_probability_percent,

		public float  $snow_probability_percent,

		public float  $wind_direction_degrees,

		/**
		 * @var float km/h / miles/h
		 */
		public float  $wind_speed

	) {
	}
}
