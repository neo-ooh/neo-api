<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_21_110010_add_product_locations_format.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products_locations', function (Blueprint $table) {
            $table->foreignId("format_id")
                  ->nullable()
                  ->after("location_id")
                  ->constrained("formats", "id")
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });
    }
};
