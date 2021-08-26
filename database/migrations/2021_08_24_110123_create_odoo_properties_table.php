<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_24_110123_create_odoo_properties_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooPropertiesTable extends Migration {
    public function up() {
        Schema::create('odoo_properties', function (Blueprint $table) {
            $table->foreignId("property_id")->primary()->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedBigInteger("odoo_id");
            $table->string("internal_name", 64)->comment("Name of the property in Odoo");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('odoo_properties');
    }
}
