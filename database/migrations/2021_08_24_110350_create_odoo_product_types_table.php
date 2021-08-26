<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_24_110350_create_odoo_product_types_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooProductTypesTable extends Migration {
    public function up() {
        Schema::create('odoo_product_types', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger("odoo_id");
            $table->string("name", 64);
            $table->string("internal_name", 64)->comment("Name of the category in Odoo");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('odoo_product_types');
    }
}
