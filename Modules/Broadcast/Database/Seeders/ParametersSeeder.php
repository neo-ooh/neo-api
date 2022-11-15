<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ParametersSeeder.php
 */

namespace Neo\Modules\Broadcast\Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;

class ParametersSeeder extends Seeder {
    public function run(): void {
        \Neo\Helpers\ParametersSeeder::seed(BroadcastParameters::class);
    }
}
