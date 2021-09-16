<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_16_140614_add_final_traffic_column_to_properties_traffic_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalTrafficColumnToPropertiesTrafficTable extends Migration {
    public function up() {
        Schema::table('properties_traffic', function (Blueprint $table) {
            $table->unsignedBigInteger("final_traffic")->virtualAs("IFNULL(`traffic`, `temporary`)")->after("temporary");
        });
    }

    public function down() {
        Schema::table('properties_traffic', function (Blueprint $table) {
            //
        });
    }
}
