<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFramesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('frames',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId("format_id")->constrained("formats")->onDelete("cascade");
                $table->string("name", 64);
                $table->unsignedInteger("width");
                $table->unsignedInteger("height");
                $table->softDeletes();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('frames');
    }
}
