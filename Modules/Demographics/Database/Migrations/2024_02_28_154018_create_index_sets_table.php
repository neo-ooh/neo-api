<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_28_154018_create_index_sets_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_ooh";
    public function up(): void {
        Schema::create('index_sets', function (Blueprint $table) {
            $table->id();

            $table->foreignId("template_id")->constrained("index_sets_templates", "id")->cascadeOnDelete();
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnDelete();
            $table->foreignId("primary_extract_id");
            $table->foreignId("reference_extract_id");
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
        Schema::dropIfExists('index_sets');
    }
};
