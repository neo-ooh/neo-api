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

class CreateSchedulesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('schedules',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId("campaign_id")->index()->constrained("campaigns")->cascadeOnDelete();
                $table->foreignId("content_id")->index()->constrained("contents")->cascadeOnDelete();
                $table->foreignId("owner_id")->index()->constrained("actors")->cascadeOnDelete();
                $table->unsignedInteger("broadsign_schedule_id")->nullable()->default(null);
                $table->timestamp("start_date")->nullable();
                $table->timestamp("end_date")->nullable();
                $table->unsignedInteger("order")->default("999");
                $table->boolean("locked")->default("0");
                $table->unsignedInteger("print_count")->default("0");
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
        Schema::dropIfExists('schedules');
    }
}
