<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_09_13_152022_create_pricelists_products_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricelistsProductsTable extends Migration {
    public function up() {
        Schema::create('pricelists_products', function (Blueprint $table) {
            $table->foreignId("pricelist_id")->constrained("pricelists", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->enum("pricing", ["unit", "cpm"]);
            $table->unsignedDouble("value");
            $table->unsignedDouble("min")->nullable();
            $table->unsignedDouble("max")->nullable();

            $table->timestamps();
        });
    }
}
