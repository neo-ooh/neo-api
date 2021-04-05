<?php


namespace Neo\Services\Weather;

interface WeatherService {
    function getCurrentWeather(Location $location, string $locale);

    function getHourlyWeather(Location $location, string $locale);

    function getForecastWeather(Location $location, string $locale);
}
