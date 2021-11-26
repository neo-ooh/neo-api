<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_26_135300_create_opening_hours_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpeningHoursTable extends Migration {
    public function up() {
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->foreignId("property_id")
                  ->constrained("properties", "actor_id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger("weekday");
            $table->time("open_at");
            $table->time("close_at");

            $table->timestamps();

            $table->primary(["property_id", "weekday"]);
        });
    }

    public function down() {
        Schema::dropIfExists('opening_hours');
    }
}
