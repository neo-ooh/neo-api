<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_14_163537_alter_properties_table_add_pricelist.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId("pricelist_id")
                  ->nullable()
                  ->after("network_id")
                  ->constrained("pricelists", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    public function down() {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn("pricelist_id");
        });
    }
};
