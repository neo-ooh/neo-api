<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_21_162929_alter_properties_fields_values_table_add_index_column.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('properties_fields_segments_values', function (Blueprint $table) {
            $table->unsignedInteger("index")->after("reference_value")->storedAs("ROUND(value / reference_value * 100)");
        });
    }

    public function down() {
        Schema::table('properties_fields_segments_values', function (Blueprint $table) {
            $table->dropColumn("index");
        });
    }
};
