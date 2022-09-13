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
use Neo\Models\Param;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;

class ParametersSeeder extends Seeder {
    public function run() {
        foreach (BroadcastParameters::cases() as $paramCase) {
            /** @var Param $param */
            $param = Param::query()->firstOrCreate([
                "slug" => $paramCase->value
            ], [
                "format" => $paramCase->format(),
                "value"  => $paramCase->defaultValue(),
            ]);

            if ($param->format !== $paramCase->format()) {
                $param->format = $paramCase->format();
                $param->save();
            }
        }
    }
}
