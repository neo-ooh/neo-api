<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_11_104041_create_external_resources_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('external_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId("resource_id")->constrained("broadcast_resources", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("broadcaster_id")
                  ->constrained("broadcasters_connections", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->json("data");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('broadcast_resources');
    }
};
