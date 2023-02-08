<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_08_095446_create_properties_translations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('properties_translations', static function (Blueprint $table) {
            $table->foreignId("property_id")
                  ->constrained("properties", "actor_id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string("locale", 5);
            $table->text("description");

            $table->timestamp("created_at")->nullable();
            $table->foreignId("created_by")->nullable()
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->timestamp("updated_at")->nullable();
            $table->foreignId("updated_by")->nullable()
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->primary(["property_id", "locale"]);
        });
    }
};
