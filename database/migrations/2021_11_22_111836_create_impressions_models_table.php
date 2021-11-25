<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_22_111836_create_impressions_models_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpressionsModelsTable extends Migration {
    public function up() {
        Schema::create('impressions_models', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger("start_month")->default(1);
            $table->unsignedInteger("end_month")->default(12);
            $table->text("formula");
            $table->json("variables");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('impressions_models');
    }
}
