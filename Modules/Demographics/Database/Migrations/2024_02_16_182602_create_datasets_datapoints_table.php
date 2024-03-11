<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_16_182602_create_datasets_datapoints_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('datasets_datapoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId("dataset_version_id")->constrained("datasets_versions", "id")->cascadeOnDelete();
            $table->string("code", 16)->index();
            $table->string("type", 32)->index();
            $table->string("label_en", 256);
            $table->string("label_fr", 256);
            $table->foreignId("reference_datapoint_id")->constrained("datasets_datapoints", "id")->nullOnDelete();

            $table->unique(["dataset_version_id", "code"]);
        });
    }

    public function down(): void {
        Schema::dropIfExists('datasets_datapoints');
    }
};
