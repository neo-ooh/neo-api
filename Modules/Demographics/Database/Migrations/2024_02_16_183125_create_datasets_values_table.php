<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_16_183125_create_datasets_values_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('datasets_values', static function (Blueprint $table) {
            $table->foreignId("datapoint_id")->constrained("datasets_datapoints", "id")->cascadeOnDelete();
            $table->foreignId("area_id")->index()->constrained("areas", "id");
            $table->double("value");
            $table->double("reference_value")->nullable();
        });

        Schema::table('datasets_values', static function (Blueprint $table) {
            $table->primary(["area_id", "datapoint_id"]);
        });
    }

    public function down(): void {
        Schema::dropIfExists('datasets_values');
    }
};
