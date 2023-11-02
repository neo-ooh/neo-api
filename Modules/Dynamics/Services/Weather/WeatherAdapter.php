<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherAdapter.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

interface WeatherAdapter {
	public function getWeather(float $lng, float $lat, string $locale): WeatherReport;
}
