<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_16_182122_create_datasets_versions_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('datasets_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId("dataset_id")->constrained("datasets", "id")->cascadeOnDelete();
            $table->string("name", 64);
            $table->string("provider", 64);
            $table->string("structure", 16)->default("FLAT");
            $table->unsignedInteger("order")->default(0);
            $table->boolean("is_primary")->default(false);
            $table->boolean("is_archived")->default(false);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('datasets_versions');
    }
};
