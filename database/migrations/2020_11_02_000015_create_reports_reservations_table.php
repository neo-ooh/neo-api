<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_11_02_000015_create_reports_reservations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsReservationsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('reports_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId("broadsign_reservation_id")->index();
            $table->foreignId('report_id')
                  ->index()
                  ->constrained('reports')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->string("name", 256);
            $table->string("internal_name", 256);
            $table->timestamp("start_date");
            $table->timestamp("end_date");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('reports_reservations');
    }
}
