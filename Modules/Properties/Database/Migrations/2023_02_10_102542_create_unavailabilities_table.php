<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_10_102542_create_unavailabilities_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('unavailabilities', function (Blueprint $table) {
            $table->id();

            $table->date("start_date")->nullable()->default(null);
            $table->date("end_date")->nullable()->default(null);

            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");
            $table->timestamp("deleted_at")->nullable(true)->default(null);
            $table->foreignId("deleted_by")->nullable(true)->default(null);
        });

        Schema::create('unavailabilities_translations', function (Blueprint $table) {
            $table->foreignId("unavailability_id")->constrained("unavailabilities", "id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->string("locale", 5);
            $table->string("reason", 255);
            $table->text("comment");

            $table->primary(["unavailability_id", "locale"]);
        });

        Schema::create("properties_unavailabilities", function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("unavailability_id")->constrained("unavailabilities", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create("products_unavailabilities", function (Blueprint $table) {
            $table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("unavailability_id")->constrained("unavailabilities", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('unavailabilities');
    }
};
