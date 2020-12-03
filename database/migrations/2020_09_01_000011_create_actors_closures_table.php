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

class CreateActorsClosuresTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void {
		Schema::create('actors_closures', function(Blueprint $table)
		{
            $table->foreignId('ancestor_id')->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('descendant_id')->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('depth')->index();

            $table->primary([ "ancestor_id", "descendant_id" ]);
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void {
		Schema::dropIfExists('actors_closures');
	}

}
