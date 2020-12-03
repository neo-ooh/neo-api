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

class CreateBurstsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('bursts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->index()->constrained('players')->cascadeOnDelete();
            $table->foreignId("requested_by")->nullable()->constrained('actors')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->string('status', 32);
            $table->boolean('is_manual');
            $table->tinyInteger('scale_factor');
            $table->integer('duration_ms');
            $table->integer('frequency_ms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('bursts');
    }
}
