<?php

namespace Neo\Services\Weather;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use JsonException;
use Neo\Exceptions\InvalidLocationException;

class MeteoMediaInterface implements WeatherService {
    protected string $endpoint;

    public const ENDPOINT_OBS = ["id" => "obs", "url" => "/Observations"];
    public const ENDPOINT_LNG = ["id" => "lng", "url" => "/LongTermForecasts"];
    public const ENDPOINT_HLY = ["id" => "hly", "url" => "/HourlyForecasts"];

    public function __construct() {
        $this->endpoint = config("services.meteo-media.endpoint");
    }


    public function getCurrentWeather(Location $location, string $locale) {
        return $this->getRecord(self::ENDPOINT_OBS, $location, $locale);
    }

    public function getHourlyWeather(Location $location, string $locale) {
        return $this->getRecord(self::ENDPOINT_HLY, $location, $locale);
    }

    public function getForecastWeather(Location $location, string $locale) {
        return $this->getRecord(self::ENDPOINT_LNG, $location, $locale);
    }

    /**
     * @param          $endpoint
     * @param Location $location
     * @param string   $locale
     * @return mixed
     * @throws InvalidLocationException
     * @throws JsonException
     */
    private function getRecord($endpoint, Location $location, string $locale) {
        // get the fully-formed endpoint url
        // Since all informations to the API are sent through the URL, we can use it as a key for caching the response
        $url = $this->buildURL($endpoint["url"], $location, $locale);

        $record = Cache::store("weather-cache")->remember($url, 2700, function () use ($url) {
            $client = new Client();
            $res    = $client->request('GET', $url);

            // Error
            if ($res->getStatusCode() !== 200) {
                return null;
            }

            // Here's our response
            return $res->getBody()->getContents();
        });

        return json_decode($record, true, 512, JSON_THROW_ON_ERROR);
    }

    private function buildURL($path, Location $location, string $locale) {
        [$country, $province, $city] = $location->getSanitizedValues();

        if (!$country || !$province || !$city) {
            throw new InvalidLocationException($location);
        }

        $url = $this->endpoint;
        $url .= $path;
        $url .= "/" . $country;
        $url .= "/" . $province;
        $url .= "/" . $city;
        $url .= "?user_key=" . config('services.meteo-media.key');
        $url .= "&locale=" . $locale;

        return $url;
    }
}
