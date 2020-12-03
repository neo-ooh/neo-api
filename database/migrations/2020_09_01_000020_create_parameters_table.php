<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

/** @noinspection PhpIllegalPsrClassPathInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('parameters', function (Blueprint $table) {
                $table->string('slug', 32)->primary();
                $table->string('format', 16);
                $table->integer('value')->nullable();
                $table->timestamps();
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::drop('parameters');
    }

}
