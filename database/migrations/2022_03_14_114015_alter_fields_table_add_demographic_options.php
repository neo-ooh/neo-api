<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_14_114015_alter_fields_table_add_demographic_options.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean("demographic_filled")->default(false)->after("is_filter");
            $table->string("visualization", 16)->nullable()->after("demographic_filled");
        });

        Schema::table('fields_segments', function (Blueprint $table) {
            $table->string("color", 6)->nullable()->after("order");
            $table->string("variable_id", 16)->nullable()->after("color");
        });

        Schema::table('properties_fields_segments_values', function (Blueprint $table) {
            $table->double("reference_value")->nullable()->after("value");
        });
    }

    public function down() {
        Schema::table("fields", function (Blueprint $table) {
            $table->dropColumn(["demographic_filled", "visualization"]);
        });

        Schema::table("fields_segments", function (Blueprint $table) {
            $table->dropColumn(["color", "variable_id"]);
        });

        Schema::table("properties_fields_segments_values", function (Blueprint $table) {
            $table->dropColumn(["reference_value"]);
        });
    }
};
