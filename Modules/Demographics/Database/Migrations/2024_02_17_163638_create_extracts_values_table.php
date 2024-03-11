<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_17_163638_create_extracts_values_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('extracts_values', function (Blueprint $table) {
            $table->foreignId("extract_id")->constrained("extracts", "id")->cascadeOnDelete();
            $table->foreignId("datapoint_id")->constrained("datasets_datapoints", "id")->cascadeOnDelete();
            $table->double("value");
        });

        Schema::table('extracts_values', function (Blueprint $table) {
            $table->primary(["extract_id", "datapoint_id"]);
        });
    }

    public function down(): void {
        Schema::dropIfExists('extracts_values');
    }
};
