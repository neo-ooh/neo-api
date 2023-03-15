<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_23_114505_create_inventory_providers_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('inventory_providers', function (Blueprint $table) {
            $table->id();
            $table->string("uuid", 64);
            $table->string("provider", 32);
            $table->string("name", 64);
            $table->boolean("is_active")->default(true);
            $table->json("settings");


            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");
            $table->timestamp("deleted_at")->nullable(true)->default(null);
            $table->foreignId("deleted_by")->nullable(true)->default(null);
        });
    }

    public function down() {
        Schema::dropIfExists('inventory_providers');
    }
};
