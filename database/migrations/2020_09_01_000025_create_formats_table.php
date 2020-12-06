<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_09_01_000025_create_formats_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormatsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('formats',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger("broadsign_display_type")->default(null);
                $table->string("slug", 256);
                $table->string("name", 256);
                $table->boolean("is_enabled")->default("1");
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
        Schema::dropIfExists('formats');
    }
}
