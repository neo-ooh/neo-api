<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatabaseSeeder.php
 */

namespace Database\Seeders;

use Database\Seeders\views\ActorsCapabilitiesViewSeeder;
use Database\Seeders\views\ActorsDetailsViewSeeder;
use Database\Seeders\views\FieldsSegmentsStatsViewSeeder;
use Illuminate\Database\Seeder as BaseSeeder;

class DatabaseSeeder extends BaseSeeder {
    public function run(): void {
        $this->call([
            // Views
            ActorsDetailsViewSeeder::class,
            ActorsCapabilitiesViewSeeder::class,
            FieldsSegmentsStatsViewSeeder::class,

            // Data
            ParamsSeeder::class,
            CapabilitiesSeeder::class,
            BootstrapSeeder::class,
        ]);
    }
}
