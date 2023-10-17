<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_10_12_145017_create_weather_bundles_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('weather_bundles', function (Blueprint $table) {
			$table->id();

			$table->string("name", 64);
			$table->foreignId("flight_id")
			      ->nullable()
			      ->default(null)
			      ->constrained("contracts_flights", "id")
			      ->cascadeOnUpdate()
			      ->nullOnDelete();
			$table->date("start_date");
			$table->date("end_date");
			$table->boolean("ignore_years");
			$table->smallInteger("priority");
			$table->string("layout", 16)->default("standard");
			$table->json("targeting")->nullable()->default(null);
			$table->string("background_selection", 16)->default("weather");

			$table->timestamp("created_at");
			$table->foreignId("created_by");
			$table->timestamp("updated_at");
			$table->foreignId("updated_by");
			$table->timestamp("deleted_at")->nullable(true)->default(null);
			$table->foreignId("deleted_by")->nullable(true)->default(null);
		});
	}
};
