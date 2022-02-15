<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_140719_create_advertisers_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisersTable extends Migration {
    public function up() {
        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();

            $table->string("name", 64);
            $table->foreignId("external_id")->nullable();

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('advertisers');
    }
}
