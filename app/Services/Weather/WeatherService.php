<?php


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
