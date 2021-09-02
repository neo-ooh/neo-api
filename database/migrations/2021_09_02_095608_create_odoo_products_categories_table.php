<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_02_095608_create_odoo_products_categories_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooProductsCategoriesTable extends Migration {
    public function up() {
        Schema::create('odoo_products_categories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger("odoo_id");
            $table->foreignId("product_type_id")->constrained("odoo_product_types")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name", 64);
            $table->string("internal_name", 64);

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('odoo_products_categories');
    }
}
