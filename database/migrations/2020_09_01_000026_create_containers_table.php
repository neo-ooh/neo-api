<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('containers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unsigned()->primary();
            $table->foreignId('parent_id')->index()->nullable()->constrained('containers')->cascadeOnDelete();
            $table->string('name', 256);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('containers');
    }
}
