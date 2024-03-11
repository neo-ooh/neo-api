<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_28_154438_create_index_sets_values_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_ooh";

    public function up(): void {
        Schema::create('index_sets_values', function (Blueprint $table) {
            $table->foreignId("set_id")->constrained("index_sets", "id")->cascadeOnDelete();
            $table->foreignId("datapoint_id");
            $table->double("primary_value");
            $table->double("reference_value");
            $table->unsignedBigInteger("index");
        });
    }

    public function down(): void {
        Schema::dropIfExists('index_sets_values');
    }
};
