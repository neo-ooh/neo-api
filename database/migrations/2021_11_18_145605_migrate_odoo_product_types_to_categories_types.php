<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_18_145605_migrate_odoo_product_types_to_categories_types.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Neo\Models\ProductType;

class MigrateOdooProductTypesToCategoriesTypes extends Migration {
    public function up() {
        Schema::rename("odoo_product_types", "categories_types");

        Schema::table('categories_types', function (Blueprint $table) {
            $table->renameColumn("odoo_id", "external_id");
            $table->renameColumn("name", "name_en");
            $table->dropColumn("internal_name");
        });

        Schema::table('categories_types', function (Blueprint $table) {
            $table->string("name_fr", 64)->after("name_en");
        });

        ProductType::all()->each(function ($ct) {
            $ct->name_fr = $ct->name_en;
            $ct->save();
        });
    }

    public function down() {
    }
}
