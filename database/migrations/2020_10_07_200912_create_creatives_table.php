<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_10_07_200912_create_creatives_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreativesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('creatives',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger("broadsign_ad_copy_id")->nullable()->default(null);
                $table->foreignId("owner_id")->constrained("actors")->cascadeOnDelete();
                $table->foreignId("content_id")->index()->constrained("contents")->cascadeOnDelete();
                $table->foreignId("frame_id")->constrained("frames")->cascadeOnDelete();
                $table->string("extension", 8);
                $table->unsignedDouble("duration")->default('0');
                $table->string("status", 64);
                $table->string("checksum", 64);
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
        Schema::dropIfExists('creatives');
    }
}
