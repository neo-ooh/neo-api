<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_10_13_144716_create_fields_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsTable extends Migration {
    public function up() {
        Schema::create('fields', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("name_en");
            $table->string("name_fr");
            $table->set("type", ["int", "float", "bool"]);
            $table->string("unit", 16);
            $table->boolean("is_filter");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('fields');
    }
}
