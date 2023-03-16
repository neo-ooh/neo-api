<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_07_151110_drop_products_types_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->dropForeign("odoo_products_categories_product_type_id_foreign");
        });
        Schema::table('products_categories', function (Blueprint $table) {
            $table->dropColumn("type_id");
        });

        Schema::table('products_types', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }
};
