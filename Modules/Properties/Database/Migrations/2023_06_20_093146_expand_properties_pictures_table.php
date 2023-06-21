<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_06_20_093146_expand_properties_pictures_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('properties_pictures', function (Blueprint $table) {
            $table->foreignId("product_id")
                  ->after("property_id")
                  ->nullable()
                  ->constrained("products", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->text("description")
                  ->after("order")
                  ->default("");
        });
    }
};
