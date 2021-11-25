<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_05_103846_alter_odoo_properties_products_table_linked_product.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOdooPropertiesProductsTableLinkedProduct extends Migration {
    public function up() {
        Schema::table('odoo_properties_products', function (Blueprint $table) {
            $table->unique("odoo_id");
            $table->unique("external_variant_id");
            $table->unsignedBigInteger("external_linked_id")
                  ->nullable()
                  ->default(null)
                  ->after("is_bonus");
            // No foreign ID here as we don't know in which order products will be inserted.
        });
    }

    public function down() {
        Schema::table('odoo_properties_products', function (Blueprint $table) {
            $table->dropColumn("external_linked_id");
        });
    }
}
