<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_06_15_104919_create_properties_traffic_snapshots_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('properties_traffic_snapshots', function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->date("date")->index();
            $table->json("traffic");

            $table->unique(["property_id", "date"]);
        });
    }

    public function down() {
        Schema::dropIfExists('properties_traffic_snapshots');
    }
};
