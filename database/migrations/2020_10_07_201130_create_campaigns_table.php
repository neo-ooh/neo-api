<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_10_07_201130_create_campaigns_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('campaigns',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger("broadsign_reservation_id")->nullable()->default(null);
                $table->foreignId("owner_id")->index()->constrained("actors")->cascadeOnDelete();
                $table->foreignId("format_id")->index()->constrained("formats")->cascadeOnDelete();
                $table->string("name", 128);
                $table->unsignedDouble("display_duration")->default("0");
                $table->unsignedInteger("content_limit");
                $table->timestamp("start_date")->nullable();
                $table->timestamp("end_date")->nullable();
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
        Schema::dropIfExists('campaigns');
    }
}
