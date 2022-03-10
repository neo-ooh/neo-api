<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_10_114214_create_demographic_variables_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('demographic_variables', function (Blueprint $table) {
            $table->string("id", 32)->primary();

            $table->string("label", 64);
            $table->set("provider", ["environics"]);

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('demographic_variables');
    }
};
