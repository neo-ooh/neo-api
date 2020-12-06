<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_09_01_000001_create_brandings_table.php
 */

/** @noinspection PhpIllegalPsrClassPathInspection */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateBrandingsTable
 */
class CreateBrandingsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('brandings',
            function (Blueprint $table) {
                $table->id();
                $table->string('name', 64);
                $table->timestamps();
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::drop('brandings');
    }

}
