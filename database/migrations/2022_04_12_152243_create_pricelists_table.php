<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_12_152243_create_pricelists_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('pricelists', function (Blueprint $table) {
            $table->id();

            $table->string("name", 64);
            $table->text("description");

            $table->timestamps();
        });

        Schema::create("pricelists_products_categories", function (Blueprint $table) {
            $table->foreignId("pricelist_id")
                  ->constrained("pricelists", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("products_category_id")
                  ->constrained("products_categories", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->set("pricing", ["unit", "cpm"]);
            $table->unsignedDouble("value");
            $table->unsignedDouble("min")->nullable();
            $table->unsignedDouble("max")->nullable();

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('pricelists');
        Schema::dropIfExists('pricelists_products_categories');
    }
};
