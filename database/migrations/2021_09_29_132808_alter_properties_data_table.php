<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_29_132808_alter_properties_data_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPropertiesDataTable extends Migration {
    public function up() {
        Schema::table('properties_data', function (Blueprint $table) {
            $table->unsignedFloat("visit_length")->nullable()->change();
            $table->unsignedFloat("spending_per_visit")->nullable()->change();
        });
    }

    public function down() {
        Schema::table('properties_data', function (Blueprint $table) {
            //
        });
    }
}
