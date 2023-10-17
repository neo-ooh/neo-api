<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherForecastDay.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

use Spatie\LaravelData\Data;

class WeatherForecastDay extends Data {
	public function __construct(
		public string           $date,

		/**
		 * @var 'metric'|'imperial' Celsius/Fahrenheit
		 */
		public string           $units,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float            $temperature_min,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float            $temperature_max,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float            $feels_like_min,

		/**
		 * @var float Celsius/Fahrenheit
		 */
		public float            $feels_like_max,

		/**
		 * @var string
		 */
		public WeatherCondition $condition,

		/**
		 * @var string
		 */
		public string           $condition_string,

		/**
		 * @var float
		 */
		public float            $cloud_coverage_percent,

		public float            $atmospheric_pressure,
		public float            $humidity_percent,

		public float            $rain_probability_percent,

		public float            $snow_probability_percent,

		public float            $wind_direction_degrees,

		/**
		 * @var float km/h / miles/h
		 */
		public float            $wind_speed

	) {
	}
}
