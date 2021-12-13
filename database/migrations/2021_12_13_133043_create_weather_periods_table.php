<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_12_13_133043_create_weather_periods_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeatherPeriodsTable extends Migration {
    public function up() {
        Schema::create('weather_periods', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("name", 64);
            $table->timestamp("start_date");
            $table->timestamp("end_date");
            $table->set("selection_method", ["WEATHER", "RANDOM"]);

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('weather_periods');
    }
}
