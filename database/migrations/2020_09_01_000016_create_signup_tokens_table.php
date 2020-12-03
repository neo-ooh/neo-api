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

class CreateSignupTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void {
		Schema::create('signup_tokens', function(Blueprint $table)
		{
            $table->id('actor_id');
            $table->string('token', 32);
            $table->timestamp('created_at');
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void {
		Schema::dropIfExists('signup_tokens');
	}

}
