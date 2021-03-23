<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_10_07_200554_create_contents_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('contents',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId("owner_id")->index()->constrained("actors")->cascadeOnDelete();
                $table->foreignId("library_id")->index()->constrained("libraries")->cascadeOnDelete();
                $table->foreignId("format_id")->index()->constrained("formats")->cascadeOnDelete();
                $table->unsignedInteger("broadsign_content_id")->nullable()->default(null);
                $table->unsignedInteger('broadsign_bundle_id')->nullable()->default(null);
                $table->string("name", 64)->nullable();
                $table->unsignedDouble("duration")->default('0');
                $table->unsignedInteger("scheduling_duration")->default("0");
                $table->unsignedInteger("scheduling_times")->default("0");
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('contents');
    }
}
