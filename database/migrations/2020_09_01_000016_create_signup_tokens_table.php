<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_09_01_000016_create_signup_tokens_table.php
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
            $table->foreignId('actor_id')->constrained("actors")->cascadeOnUpdate()->cascadeOnDelete();
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
