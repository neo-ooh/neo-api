<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_10_07_202117_create_checksum_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecksumTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('checksum',
            function (Blueprint $table) {
                $table->string("checksum", 40)->unique();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('checksum');
    }
}
