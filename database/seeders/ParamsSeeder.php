<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ParamsSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Models\Param;

class ParamsSeeder extends Seeder {
    public static function run (): void {
        // Terms of service
        Param::insertOrIgnore([
            "slug" => "tos",
            "format" => "file:pdf",
            "value" => ""
        ]);

        // English welcome text
        Param::insertOrIgnore([
            "slug" => "WELCOME_TEXT_EN",
            "format" => "text",
            "value" => "..."
        ]);

        // French welcome text
        Param::insertOrIgnore([
            "slug" => "WELCOME_TEXT_FR",
            "format" => "text",
            "value" => "..."
        ]);
    }
}
