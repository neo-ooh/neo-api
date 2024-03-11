<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2024_02_26_155254_create_demographic_properties_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = "neo_demographics";

    public function up(): void {
        Schema::create('properties', function (Blueprint $table) {
            $table->unsignedBigInteger("id")->primary();
            $table->boolean("is_archived")->index();
            $table->string("name");
        });
    }

    public function down(): void {
        Schema::dropIfExists('properties');
    }
};
