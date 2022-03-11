<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_11_100406_create_fields_categories_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('fields_categories', function (Blueprint $table) {
            $table->id();

            $table->string("name_en", 64);
            $table->string("name_fr", 64);

            $table->timestamps();
        });

        Schema::table('fields', function (Blueprint $table) {
            $table->foreignId("category_id")->after("id")->nullable()->constrained("fields_categories", "id");
        });
    }

    public function down() {
        Schema::dropIfExists('fields_categories');
        Schema::dropColumns("fields", ["category_id"]);
    }
};
