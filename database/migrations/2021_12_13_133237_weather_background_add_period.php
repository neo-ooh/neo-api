<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_12_13_133237_weather_background_add_period.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WeatherBackgroundAddPeriod extends Migration {
    public function up() {
        Schema::table('weather_backgrounds', function (Blueprint $table) {
            $table->renameColumn("period", "day_part");
        });

        Schema::table('weather_background', function (Blueprint $table) {
            $table->foreignId("period_id")->constrained("weather_periods")->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down() {
    }
}
