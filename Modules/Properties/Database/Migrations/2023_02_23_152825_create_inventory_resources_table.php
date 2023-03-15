<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_23_152825_create_inventory_resources_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('inventory_resources', function (Blueprint $table) {
            $table->id();
            $table->string("type", 32);
        });

        Schema::table("properties", function (Blueprint $table) {
            $table->foreignId("inventory_resource_id")
                  ->after("actor_id")
                  ->nullable()
                  ->constrained("inventory_resources", "id")
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });

        Schema::table("products", function (Blueprint $table) {
            $table->foreignId("inventory_resource_id")
                  ->after("id")
                  ->nullable()
                  ->constrained("inventory_resources", "id")
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });


    }

    public function down() {
        Schema::dropIfExists('inventory_resources');
    }
};
