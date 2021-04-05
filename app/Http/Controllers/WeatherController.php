<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WeatherController extends Controller
{
    /**
     * List of canadian provinces 2-letters names
     */
    protected const PROVINCES = ["NL", "PE", "NS", "NB", "QC", "ON", "MB", "SK", "AB", "BC", "YT", "NT", "NU"];

    /**
     * Full name of canadian provinces keying their abbreviated names
     */
    protected const PROVINCES_LNG = [
        "Terre-Neuve-et-Labrador" => "NL",
        "Île-du-Prince-Édouard" => "PE",
        "Nouvelle-Écosse" => "NS",
        "Nouveau-Brunswick" => "NB",
        "Québec" => "QC",
        "Ontario" => "ON",
        "Manitoba" => "MB",
        "Saskatchewan" => "SK",
        "Alberta" => "AB",
        "British Columbia" => "BC",
        "Yukon" => "YT",
        "Northwest Territories" => "NT",
        "Nunavut" => "NU"
    ];

    /**
     * List of mapping between cities.
     * This used for specific cases where an actual town is actyally referrenced under another name in meteo-media
     *
     * @todo Move this part of the code to the MeteMedia part of the website
     */
    protected const CITIES = [
        "Ville de Québec" => "Québec",
        "Boulevard Laurier" => "Québec",
    ];

    private $cities = [
        ["CA", "ON", "Toronto"],
        ["CA", "ON", "Ottawa"],
        ["CA", "QC", "Montreal"],
        ["CA", "QC", "Quebec"],
        ["CA", "NS", "Halifax"],
        ["CA", "BC", "Victoria"],
        ["CA", "BC", "Vancouver"],
        ["CA", "AB", "Calgary"],
        ["CA", "AB", "Edmonton"],
        ["CA", "MB", "Winnipeg"],
    ];

    /**
     * Gives the national weather
     * @return Response
     */
    public function national(): Response
    {
        $forecasts = [];
        $link = new MeteoMediaLinkService();

        $locale = Request('locale', 'en-CA');

        foreach ($this->cities as $city) {
            $forecasts[] = $link->getNow($locale, ...$city);
        }

        return new Response($forecasts);
    }

    /**
     * Give the current weather for the specified city
     *
     * @param Request $request
     * @return Response
     */
    public function now(Request $request): Response
    {
        $country = $request->country;
        $province = $request->province;
        $city = $request->city;

        $this->sanitizeLocation($country, $province, $city);
        if(!$country || !$province || !$city) {
            return new Response(null);
        }

        // Request
        $link = new MeteoMediaLinkService();
        $locale = $request->input('locale', 'en-CA');

        $now = $link->getNow($locale, $country, $province, $city);
        $longTerm = $link->getNext($locale, $country, $province, $city)["LongTermPeriod"][0];

        $forecast = array_merge($longTerm, $now);

        return new Response($forecast);
    }

    /**
     * Give the next day weather for the specified location
     * @param Request $request The request
     * @return Response
     */
    public function tomorrow(Request $request): Response
    {
        $country = $request->country;
        $province = $request->province;
        $city = $request->city;

        $this->sanitizeLocation($country, $province, $city);
        if(!$country || !$province || !$city) {
            return new Response(null);
        }

        $link = new MeteoMediaLinkService();
        $locale = $request->input('locale', 'en-CA');

        $longTerm = $link->getNext($locale, $country, $province, $city);

        if($longTerm == null) {
            return new Response(null);
        }

        $forecast = $longTerm["LongTermPeriod"][1];
        $forecast["Location"] = $longTerm["Location"];

        return new Response($forecast);
    }

    /**
     * Give the seven days weather for the specified location
     * @param Request $request The request
     * @return Response
     */
    public function forecast(Request $request): Response
    {
        $country = $request->country;
        $province = $request->province;
        $city = $request->city;

        $this->sanitizeLocation($country, $province, $city);
        if(!$country || !$province || !$city) {
            return new Response(null);
        }

        $link = new MeteoMediaLinkService();
        $locale = $request->input('locale', 'en-CA');

        $forecast = $link->getNext($locale, $country, $province, $city);

        if($forecast != null) {
            array_splice($forecast["LongTermPeriod"], 0, 1);
        }

        return new Response($forecast);
    }

    /**
     * Give the next hours weather forecast for the specified location
     * @param Request $request The request
     * @return Response
     */
    public function hourly(Request $request): Response
    {
        $country = $request->country;
        $province = $request->province;
        $city = $request->city;

        $this->sanitizeLocation($country, $province, $city);
        if(!$country || !$province || !$city) {
            return new Response(null);
        }

        $link = new MeteoMediaLinkService();
        $locale = $request->input('locale', 'en-CA');

        $hourly = $link->getHourly($locale, $country, $province, $city);

        return new Response($hourly);
    }

    private function sanitizeLocation(?String &$country, ?String &$province, ?String &$city) {
        // Check if the location is valid
        if($country !== "CA") {
            $country = null;
            return;
        }

        if(array_key_exists($province, self::PROVINCES_LNG)) {
            $province = self::PROVINCES_LNG[$province];
        } else if(!in_array($province, self::PROVINCES, true)) {
            $province = null;
        }

        // Make sure the city format is valid
        $city = str_replace(".", "", urldecode($city));
        if(array_key_exists($city, self::CITIES)) {
            $city = self::CITIES[$city];
        }

        if($city === "Repentigny") {
            $province = 'QC';
        }
    }
}
