<?php


namespace Neo\Services\Weather;

use Neo\Exceptions\InvalidLocationException;

interface WeatherService {
    /**
     * @param Location $location
     * @param string   $locale
     * @throws InvalidLocationException
     * @return mixed
     */
    public function getCurrentWeather(Location $location, string $locale);

    public function getHourlyWeather(Location $location, string $locale);

    public function getForecastWeather(Location $location, string $locale);
}
