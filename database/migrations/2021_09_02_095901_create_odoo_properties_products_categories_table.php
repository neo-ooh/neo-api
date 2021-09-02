<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_02_095901_create_odoo_properties_products_categories_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooPropertiesProductsCategoriesTable extends Migration {
    public function up() {
        Schema::create('odoo_properties_products_categories', function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("odoo_properties", "property_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("product_category_id")->constrained("odoo_products_categories")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('odoo_properties_products_categories');
    }
}
