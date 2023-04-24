<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherLocation.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Exceptions\InvalidLocationException;

/**
 * Class WeatherLocation
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $background_selection
 * @property Date   $selection_revert_date
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class WeatherLocation extends Model {
    public const NULL_COMPONENT = "-";

    protected $table = "weather_locations";

    protected $fillable = [
        'country',
        'province',
        'city',
        'background_selection',
        'selection_revert_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "selection_revert_date" => "datetime",
    ];

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
        "Nunavut"                 => "NU",
    ];

    /**
     * List of mapping between cities.
     * This used for specific cases where an actual town is actually referrenced under another name in meteo-media
     */
    public const CITIES = [
        "Ville de Québec"   => "Québec",
        "Boulevard Laurier" => "Québec",
    ];

    public function backgrounds(): HasMany {
        return $this->hasMany(WeatherBackground::class, 'weather_location_id');
    }

    /**
     * @param string  $country
     * @param string  $province
     * @param string  $city
     * @param boolean $allowIncomplete If set to true, no Exception will be raised if the location is not complete.
     * @return WeatherLocation
     * @throws InvalidLocationException
     */
    public static function fromComponents(string $country, string $province, string $city, $allowIncomplete = false): WeatherLocation {

        [$country, $province, $city] = static::sanitizeValues($country, $province, $city, $allowIncomplete);

        return static::query()
                     ->firstOrCreate([
                                         "country" => $country,
                                                                                                                                                                                                                                                                                                                          "province" => $province,
                                         "city"    => $city,
                                     ], [
                                         'background_selection'  => "WEATHER",
                                         'selection_revert_date' => null,
                                     ]);
    }

    /**
     * @returns [string $country, string $province, string $city]
     * @throws InvalidLocationException
     */
    public static function sanitizeValues(string $country, string $province, string $city, $allowIncomplete = false): array {
        $country  = strtoupper($country);
        $province = strtoupper($province);

        // Check if the location is valid
        if ($country !== "CA") {
            // Only Canada is supported as of writing (04/21)
            throw new InvalidLocationException($country, $province, $city);
        }

        if (array_key_exists($province, self::PROVINCES_LNG)) {
            $province = self::PROVINCES_LNG[$province];
        } else if (!$allowIncomplete && !in_array($province, self::PROVINCES, true)) {
            throw new InvalidLocationException($country, $province, $city);
        }

        // Make sure the city format is valid
        $city = str_replace(".", "", urldecode($city));
        if (array_key_exists($city, self::CITIES)) {
            $city = self::CITIES[$city];
        }

        if ($city === "Repentigny") {
            $province = 'QC';
        }

        if (!$allowIncomplete && (!$province || !$city)) {
            throw new InvalidLocationException($country, $province, $city);
        }

        return [$country, $province, $city];
    }
}
