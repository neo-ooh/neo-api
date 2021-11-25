<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_04_152430_alter_odoo_properties_products_table_is_bonus.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOdooPropertiesProductsTableIsBonus extends Migration {
    public function up() {
        Schema::table('odoo_properties_products', function (Blueprint $table) {
            $table->boolean("is_bonus")->default(0)->after("external_variant_id");
        });
    }

    public function down() {
        Schema::table('odoo_properties_products', function (Blueprint $table) {
            $table->dropColumn("is_bonus");
        });
    }
}
