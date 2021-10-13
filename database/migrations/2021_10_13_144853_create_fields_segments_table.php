<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_10_13_144853_create_fields_segments_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsSegmentsTable extends Migration {
    public function up() {
        Schema::create('fields_segments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId("field_id")->constrained("fields")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name_en");
            $table->string("name_fr");
            $table->unsignedInteger("order");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('fields_segments');
    }
}
