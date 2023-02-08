<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_08_095942_add_properties_fields.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('properties', static function (Blueprint $table) {
            $table->string("website", 255)->default("")->after("has_tenants");

            $table->foreignId("created_by")
                  ->after("created_at")
                  ->nullable()
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId("updated_by")
                  ->after("updated_at")
                  ->nullable()
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->softDeletes();
            $table->foreignId("deleted_by")
                  ->nullable()
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
};
