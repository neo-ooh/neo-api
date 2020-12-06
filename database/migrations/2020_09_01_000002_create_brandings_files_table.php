<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_09_01_000002_create_brandings_files_table.php
 */

/** @noinspection PhpIllegalPsrClassPathInspection */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandingsFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('brandings_files',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId('branding_id')->constrained('brandings')->cascadeOnDelete();
                $table->string('type', 16)->nullable(false);
                $table->string('filename', 32)->nullable(false);
                $table->string('original_name', 64)->nullable(false);
                $table->timestamps();
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('brandings_files');
    }

}
