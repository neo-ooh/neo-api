<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_09_01_000019_create_two-factor-tokens_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoFactorTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void {
		Schema::create('two-factor-tokens', function(Blueprint $table)
		{
            $table->foreignId('actor_id')->constrained('actors')->cascadeOnDelete();
            $table->string('token', 6)->nullable();
            $table->boolean('validated')->default(0);
            $table->timestamp('created_at');
            $table->timestamp('validated_at')->nullable();
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void {
		Schema::drop('two-factor-tokens');
	}

}
