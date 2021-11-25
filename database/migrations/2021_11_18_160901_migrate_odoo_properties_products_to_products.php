<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_18_160901_migrate_odoo_properties_products_to_products.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Product;

class MigrateOdooPropertiesProductsToProducts extends Migration {
    public function up() {
        Schema::rename("odoo_properties_products", "products");

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn("product_category_id", "category_id");
            $table->renameColumn("odoo_id", "external_id");
            $table->renameColumn("odoo_variant_id", "external_variant_id");
            $table->renameColumn("linked_product_id", "external_linked_id");
            $table->renameColumn("name", "name_en");
        });

        Schema::table('products', function (Blueprint $table) {
            $table->id()->first();
            $table->string("name_fr", 64)->after("name_en");
        });

        Product::all()->each(function ($p) {
            $p->name_fr = $p->name_en;
            $p->save();
        });
    }

    public function down() {
    }
}
