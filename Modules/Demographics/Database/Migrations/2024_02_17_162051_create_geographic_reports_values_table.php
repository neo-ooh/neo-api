<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_17_162051_create_geographic_reports_values_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('geographic_reports_values', static function (Blueprint $table) {
            $table->foreignId("report_id")->index()->constrained("geographic_reports", "id")->cascadeOnDelete();
            $table->foreignId("area_id")->constrained("areas", "id")->cascadeOnDelete();

            $table->double("geography_weight");
            $table->json("metadata");
        });

        Schema::table('geographic_reports_values', static function (Blueprint $table) {
            $table->primary(["report_id", "area_id"]);
        });
    }

    public function down(): void {
        Schema::dropIfExists('geographic_reports_values');
    }
};
