<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_10_01_193014_create_libraries_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrariesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create("libraries",
            function (Blueprint $table) {
                $table->id();
                $table->foreignId("owner_id")->nullable(false)->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string("name", 64)->default("");
                $table->unsignedInteger("content_limit")->default("0");
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists("libraries");
    }
}
