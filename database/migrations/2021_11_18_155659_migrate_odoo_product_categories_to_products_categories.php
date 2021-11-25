<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_18_155659_migrate_odoo_product_categories_to_products_categories.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Neo\Enums\ProductsFillStrategy;
use Neo\Models\ProductCategory;

class MigrateOdooProductCategoriesToProductsCategories extends Migration {
    public function up() {
        Schema::rename("odoo_products_categories", "products_categories");

        Schema::table('products_categories', function (Blueprint $table) {
            $table->renameColumn("odoo_id", "external_id");
            $table->renameColumn("product_type_id", "type_id");
            $table->renameColumn("name", "name_en");
            $table->dropColumn(["internal_name", "quantity"]);
        });

        Schema::table('products_categories', function (Blueprint $table) {
            $table->string("name_fr", 64)->after("name_en");
            $table->set("fill_strategy", ProductsFillStrategy::getValues())
                  ->default(ProductsFillStrategy::digital)
                  ->after("name_fr");
        });

        ProductCategory::all()->each(function ($pc) {
            $pc->name_fr = $pc->name_en;
            $pc->save();
        });
    }

    public function down() {
    }
}
