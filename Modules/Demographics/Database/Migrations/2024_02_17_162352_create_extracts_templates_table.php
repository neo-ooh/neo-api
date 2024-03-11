<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_17_162352_create_extracts_templates_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('extracts_templates', function (Blueprint $table) {
            $table->id();

            $table->string("name", 64);
            $table->text("description");
            $table->foreignId("dataset_version_id")->constrained("datasets_versions", "id")->cascadeOnDelete();
            $table->foreignId("geographic_report_template_id")->constrained("geographic_reports_templates", "id")->cascadeOnDelete();

            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");
            $table->timestamp("deleted_at")->nullable()->default(null);
            $table->foreignId("deleted_by")->nullable()->default(null);
        });
    }

    public function down(): void {
        Schema::dropIfExists('extracts_templates');
    }
};
