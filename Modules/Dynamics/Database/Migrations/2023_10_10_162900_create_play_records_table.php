<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_10_10_162900_create_play_records_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('dynamics_play_records', function (Blueprint $table) {
			$table->id();
			$table->foreignId("player_id")->constrained("players", "id")->cascadeOnUpdate()->cascadeOnDelete();
			$table->timestamp("loaded_at");
			$table->timestamp("played_at");
			$table->timestamp("ended_at");
			$table->timestamp("received_at");
			$table->string("dynamic", 32)->index();
			$table->string("version", 16)->index();
			$table->json("params");
			$table->json("logs");
		});
	}
};
