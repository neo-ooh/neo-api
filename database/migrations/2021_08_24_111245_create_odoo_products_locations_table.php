<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_24_111245_create_odoo_products_locations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooProductsLocationsTable extends Migration {
    public function up() {
        Schema::create('odoo_products_locations', function (Blueprint $table) {
            $table->foreignId("product_id")->constrained("odoo_products")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId("location_id")->constrained("locations")->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });

        Schema::table("odoo_products_locations", function (Blueprint $table) {
            $table->primary(["product_id", "location_id"]);
        });
    }

    public function down() {
        Schema::dropIfExists('odoo_products_locations');
    }
}
