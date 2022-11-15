<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ParamsSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Enums\CommonParameters;
use Neo\Helpers\ParametersSeeder;

class ParamsSeeder extends Seeder {
    public static function run(): void {
        ParametersSeeder::seed(CommonParameters::class);
    }
}
