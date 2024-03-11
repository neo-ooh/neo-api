<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_17_163258_create_extracts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('extracts', function (Blueprint $table) {
            $table->id();
            $table->string("uuid", 36);

            $table->foreignId("template_id")->constrained("extracts_templates", "id")->cascadeOnDelete();
            $table->foreignId("property_id");
            $table->foreignId("geographic_report_id")->constrained("geographic_reports", "id")->cascadeOnDelete();
            $table->json("metadata");
            $table->string("status", 16);

            $table->timestamp("requested_at");
            $table->foreignId("requested_by")->nullable();
            $table->timestamp("processed_at")->nullable();
            $table->timestamp("deleted_at")->nullable();
            $table->foreignId("deleted_by")->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('extracts');
    }
};
