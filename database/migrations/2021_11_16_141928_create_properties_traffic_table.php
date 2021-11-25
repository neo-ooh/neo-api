<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_16_141928_create_properties_traffic_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTrafficTable extends Migration {
    public function up() {
        Schema::create('properties_traffic', function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->year("year");
            $table->unsignedInteger("week");
            $table->unsignedBigInteger("traffic");
            $table->boolean("is_estimate");
        });
    }

    public function down() {
        Schema::dropIfExists('properties_traffic');
    }
}
