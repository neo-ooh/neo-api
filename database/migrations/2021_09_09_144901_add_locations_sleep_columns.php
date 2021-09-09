<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_09_144901_add_locations_sleep_columns.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationsSleepColumns extends Migration {
    public function up() {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean("scheduled_sleep")->nullable(false)->default('0')->after("city");
            $table->time("sleep_end")->nullable(true);
            $table->time("sleep_start")->nullable(true);
        });
    }

    public function down() {
    }
}
