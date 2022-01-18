<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_01_18_151922_create_brands_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration {
    public function up() {
        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("name", 64);

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('brands');
    }
}
