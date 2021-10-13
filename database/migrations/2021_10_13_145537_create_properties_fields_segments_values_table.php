<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_10_13_145537_create_properties_fields_segments_values_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesFieldsSegmentsValuesTable extends Migration {
    public function up() {
        Schema::create('properties_fields_segments_values', function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("fields_segment_id")->constrained("fields_segments")->cascadeOnUpdate()->cascadeOnDelete();

            $table->double("value");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('properties_fields_segments_values');
    }
}
