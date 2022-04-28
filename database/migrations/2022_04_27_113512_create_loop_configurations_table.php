<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_27_113512_create_loop_configurations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('loop_configurations', function (Blueprint $table) {
            $table->id();

            $table->string("name", 64);
            $table->unsignedBigInteger("loop_length_ms");
            $table->unsignedBigInteger("spot_length_ms");
            $table->unsignedInteger("reserved_spots")->default(0);
            $table->date("start_date");
            $table->date("end_date");
            $table->unsignedInteger("max_spots_count")->generatedAs("`loop_length_ms` / `spot_length_ms`");
            $table->unsignedInteger("free_spots_count")->generatedAs("`max_spots_count` / `reserved_spots`");

            $table->timestamps();
        });

        // Transient tables
        Schema::create("products_loop_configurations", function (Blueprint $table) {
            $table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("loop_configuration_id")
                  ->constrained("loop_configurations", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });

        Schema::create("products_categories_loop_configurations", function (Blueprint $table) {
            $table->foreignId("product_category_id");
            $table->foreignId("loop_configuration_id");

            $table->foreign("product_category_id", "product_category_loop_configuration_foreign")
                  ->on("products_categories")
                  ->references("id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreign("loop_configuration_id", "loop_configuration_loop_configuration_foreign")
                  ->on("loop_configurations")
                  ->references("id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });

        // Transient tables primary keys
        Schema::table("products_loop_configurations", function (Blueprint $table) {
            $table->primary(["product_id", "loop_configuration_id"], "product_id_loop_configurations_id_primary");
        });

        Schema::table("products_categories_loop_configurations", function (Blueprint $table) {
            $table->primary(["product_category_id", "loop_configuration_id"], "product_category_id_loop_configuration_id_foreign");
        });
    }

    public function down() {
        Schema::dropIfExists('loop_configurations');
    }
};
