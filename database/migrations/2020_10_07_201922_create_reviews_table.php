<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_10_07_201922_create_reviews_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('reviews',
            function (Blueprint $table) {
                $table->id();
                $table->foreignId("schedule_id")->index()->constrained("schedules")->cascadeOnDelete();
                $table->foreignId("reviewer_id")->constrained("actors")->cascadeOnDelete();
                $table->boolean("approved");
                $table->text("message")->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('reviews');
    }
}
