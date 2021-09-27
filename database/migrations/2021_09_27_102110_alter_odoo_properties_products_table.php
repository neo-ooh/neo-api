<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_27_102110_alter_odoo_properties_products_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOdooPropertiesProductsTable extends Migration {
    public function up() {
//        Schema::table('odoo_properties_products_categories', function (Blueprint $table) {
        Schema::table('odoo_properties_products', function (Blueprint $table) {
//            $table->rename("odoo_properties_products");

            $table->unsignedBigInteger("odoo_id")->after("product_category_id");
            $table->string("name")->after("odoo_id");
            $table->unsignedInteger("quantity")->default(0)->after("name");
            $table->unsignedDouble("unit_price")->default(0)->after("quantity");
            $table->unsignedBigInteger("odoo_variant_id")->nullable()->after("unit_price");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::table('odoo_properties_products_categories', function (Blueprint $table) {
            //
        });
    }
}
