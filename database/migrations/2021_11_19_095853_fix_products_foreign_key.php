<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_19_095853_fix_products_foreign_key.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixProductsForeignKey extends Migration {
    public function up() {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign("odoo_properties_products_categories_property_id_foreign");
            $table->foreign("property_id")->on("properties")->references("actor_id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
