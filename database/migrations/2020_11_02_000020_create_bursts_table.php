<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_11_02_000020_create_bursts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBurstsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('bursts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->index()->constrained('reports')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("location_id")->constrained('locations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("requested_by")->nullable()->constrained('actors')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->boolean('started');
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
