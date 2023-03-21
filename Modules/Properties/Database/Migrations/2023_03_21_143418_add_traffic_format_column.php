<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_21_143418_add_traffic_format_column.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Modules\Properties\Enums\TrafficFormat;

return new class extends Migration {
    public function up(): void {
        Schema::table('property_traffic_settings', function (Blueprint $table) {
            $table->string("format", 32)->after("property_id");
        });

        $properties = \Illuminate\Support\Facades\DB::table("properties")->get();

        foreach ($properties as $property) {
            \Illuminate\Support\Facades\DB::table("property_traffic_settings")
                                          ->where("property_id", "=", $property->actor_id)
                                          ->update([
                                                       "format" => $property->network_id === 1
                                                           ? TrafficFormat::MonthlyAdjusted->value
                                                           : TrafficFormat::MonthlyMedian->value
                                                   ]);
        }
    }
};
