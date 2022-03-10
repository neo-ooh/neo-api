<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherService.php
 */

namespace Neo\Services\Weather;

use Neo\Exceptions\InvalidLocationException;
use Neo\Models\WeatherLocation;

interface WeatherService {
    /**
     * @param string   $locale
     * @throws InvalidLocationException
     * @return mixed
     */
    public function getCurrentWeather(WeatherLocation $location, string $locale);

    public function getHourlyWeather(WeatherLocation $location, string $locale);

    public function getForecastWeather(WeatherLocation $location, string $locale);
}
