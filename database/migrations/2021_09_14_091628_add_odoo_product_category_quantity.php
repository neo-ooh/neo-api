<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_14_091628_add_odoo_product_category_quantity.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooProductCategoryQuantity extends Migration {
    public function up() {
        Schema::table('odoo_products_categories', function (Blueprint $table) {
            $table->unsignedInteger("quantity")->default(1)->after("internal_name");
        });
    }

    public function down() {
        Schema::table('odoo_products_categories', function (Blueprint $table) {
            //
        });
    }
}
