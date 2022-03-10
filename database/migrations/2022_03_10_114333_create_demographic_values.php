<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_10_114333_create_demographic_values.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('demographic_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId("property_id")->index()->constrained("properties", "actor_id");
            $table->string("value_id", 32);
            $table->float("value");
            $table->float("reference_value");

            $table->timestamps();

            $table->foreign("value_id")->references("id")->on("demographic_variables");
            $table->unique(["property_id", "value_id"]);
        });
    }

    public function down() {
        Schema::dropIfExists('demographic_values');
    }
};
