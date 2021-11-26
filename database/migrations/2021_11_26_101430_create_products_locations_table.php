<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_26_101430_create_products_locations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsLocationsTable extends Migration {
    public function up() {
        Schema::create('products_locations', function (Blueprint $table) {
            $table->foreignId("product_id")
                  ->constrained("products")
                  ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("location_id")
                  ->constrained("locations")
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('products_locations');
    }
}
