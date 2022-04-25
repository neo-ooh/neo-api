<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_21_113224_create_point_of_interests_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('points_of_interest', function (Blueprint $table) {
            $table->id();

            $table->foreignId("brand_id")->constrained("brands", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("external_id", 64)->nullable()->unique();
            $table->string("name", 128);
            $table->text("address");
            $table->point("position", "4326");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('points_of_interest');
    }
};
