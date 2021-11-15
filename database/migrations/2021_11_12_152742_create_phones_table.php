<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_12_152742_create_phones_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonesTable extends Migration {
    public function up() {
        Schema::create('phones', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("number_country");
            $table->string("number");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('phones');
    }
}
