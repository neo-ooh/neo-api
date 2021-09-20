<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_20_124852_create_properties_pictures_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesPicturesTable extends Migration {
    public function up() {
        Schema::create('properties_pictures', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("extension", 8);
            $table->string("name", 128)->default("");
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger("width");
            $table->unsignedInteger("height");
            $table->unsignedInteger("order");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('properties_pictures');
    }
}
