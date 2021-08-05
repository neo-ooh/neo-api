<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdressesComponentsSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Models\Country;
use Neo\Models\Market;
use Neo\Models\Province;

class AddressesComponentsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        // Seed countries
        $canada = Country::query()->firstOrCreate([
            "name" => "Canada",
            "slug" => "CA",
        ]);

        // See provinces
        Province::query()->insertOrIgnore([
            ["slug" => "AB", "country_id" => $canada->id, "name" => "Alberta"],
            ["slug" => "BC", "country_id" => $canada->id, "name" => "British-Columbia"],
            ["slug" => "MB", "country_id" => $canada->id, "name" => "Manitoba"],
            ["slug" => "NB", "country_id" => $canada->id, "name" => "New Brunswick"],
            ["slug" => "NL", "country_id" => $canada->id, "name" => "Newfoundland and Labrador"],
            ["slug" => "NS", "country_id" => $canada->id, "name" => "Nova Scotia"],
            ["slug" => "ON", "country_id" => $canada->id, "name" => "Ontario"],
            ["slug" => "PE", "country_id" => $canada->id, "name" => "Prince Edward Island"],
            ["slug" => "QC", "country_id" => $canada->id, "name" => "Quebec"],
            ["slug" => "SK", "country_id" => $canada->id, "name" => "Saskatchewan"],
            ["slug" => "NU", "country_id" => $canada->id, "name" => "Nunavut"],
            ["slug" => "NT", "country_id" => $canada->id, "name" => "Northwest Territories"],
            ["slug" => "YT", "country_id" => $canada->id, "name" => "Yukon"],
        ]);

        // Seed markets
        $markets = [
            "QC" => [
                ["en" => "Central Quebec", "fr" => "Centre du Québec"],
                ["en" => "Eastern Townships", "fr" => "Cantons de l'Est"],
                ["en" => "Greater Montreal Area", "fr" => "Grande Région de Montréal"],
                ["en" => "Hull - Gatineau", "fr" => "HUll - Gatineau"],
                ["en" => "North-East Quebec", "fr" => "Nord-Est du Québec"],
                ["en" => "North-West Quebec", "fr" => "Nord-Ouest du Québec"],
                ["en" => "Quebec", "fr" => "Québec"],
            ],
            "ON" => [
                ["en" => "Greater Toronto Area", "fr" => "Grande Région de Toronto"],
                ["en" => "North Western Ontario", "fr" => "Nord-Ouest Ontarien"],
                ["en" => "South Western Ontario", "fr" => "Sud-Ouest Ontarien"],
                ["en" => "Kingston - Belleville", "fr" => "Kingston - Belleville"],
                ["en" => "Ottawa", "fr" => "Ottawa"],
            ],
            "MB" => [
                ["en" => "Winnipeg & Reqions", "fr" => "Winnipeg & Réqions"],
            ],
            "SK" => [
                ["en" => "Regina & Reqions", "fr" => "Regina & Réqions"],
            ],
            "AB" => [
                ["en" => "Edmonton & Reqions", "fr" => "Edmonton & Réqions"],
                ["en" => "Calgary & Reqions", "fr" => "Calgary & Réqions"],
            ],
            "BC" => [
                ["en" => "Greater Vancouver Area", "fr" => "Grande Région de Vancouver"],
            ],
            "NB" => [
                ["en" => "Dieppe, Atholville & Bathurst", "fr" => "Dieppe, Atholville & Bathurst"],
                ["en" => "Halifax & Regions", "fr" => "Halifax & Régions"],
            ],
        ];

        foreach ($markets as $provinceCode => $ms) {
            /** @var Province $province */
            $province = Province::query()->where("slug", "=", $provinceCode)->first();
            Market::query()->insertOrIgnore(collect($ms)->map(fn($m) => [
                "province_id" => $province->id,
                "name_en" => $m["en"],
                "name_fr" => $m["fr"],
            ])->values()->toArray());
        }
    }
}
