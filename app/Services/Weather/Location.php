<?php

namespace Neo\Services\Weather;

class Location {
    public ?string $country;
    public ?string $province;
    public ?string $city;

    public function __construct(?string $country, ?string $province, ?string $city) {
        $this->country  = $country;
        $this->province = $province;
        $this->city     = $city;
    }

    /**
     * List of canadian provinces 2-letters names
     */
    public const PROVINCES = ["NL", "PE", "NS", "NB", "QC", "ON", "MB", "SK", "AB", "BC", "YT", "NT", "NU"];

    /**
     * Full name of canadian provinces keying their abbreviated names
     */
    public const PROVINCES_LNG = [
        "Terre-Neuve-et-Labrador" => "NL",
        "Île-du-Prince-Édouard"   => "PE",
        "Nouvelle-Écosse"         => "NS",
        "Nouveau-Brunswick"       => "NB",
        "Québec"                  => "QC",
        "Ontario"                 => "ON",
        "Manitoba"                => "MB",
        "Saskatchewan"            => "SK",
        "Alberta"                 => "AB",
        "British Columbia"        => "BC",
        "Yukon"                   => "YT",
        "Northwest Territories"   => "NT",
        "Nunavut"                 => "NU"
    ];

    /**
     * List of mapping between cities.
     * This used for specific cases where an actual town is actyally referrenced under another name in meteo-media
     */
    public const CITIES = [
        "Ville de Québec"   => "Québec",
        "Boulevard Laurier" => "Québec",
    ];

    /**
     * @returns [?string $country, ?string $province, ?string $city]
     */
    public function getSanitizedValues(): array {
        $country  = $this->country;
        $province = $this->province;
        $city     = $this->city;

        // Check if the location is valid
        if ($country !== "CA") {
            // Only Canada is support as of writing (04/21)
            return [null, null, null];
        }

        if (array_key_exists($province, self::PROVINCES_LNG)) {
            $province = self::PROVINCES_LNG[$province];
        } else if (!in_array($province, self::PROVINCES, true)) {
            $province = null;
        }

        // Make sure the city format is valid
        $city = str_replace(".", "", urldecode($city));
        if (array_key_exists($city, self::CITIES)) {
            $city = self::CITIES[$city];
        }

        if ($city === "Repentigny") {
            $province = 'QC';
        }

        return [$country, $province, $city];
    }
}
