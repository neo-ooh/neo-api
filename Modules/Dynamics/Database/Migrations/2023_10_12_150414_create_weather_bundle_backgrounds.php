<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_10_12_150414_create_weather_bundle_backgrounds.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('weather_bundle_backgrounds', function (Blueprint $table) {
			$table->id();

			$table->foreignId("bundle_id")->constrained("weather_bundles", "id")
			      ->cascadeOnUpdate()
			      ->restrictOnDelete();
			$table->foreignId("format_id")->constrained("formats", "id")
			      ->cascadeOnUpdate()
			      ->restrictOnDelete();
			$table->string("weather", 32)->nullable(true);
			$table->string("period", 16)->nullable(true);
			$table->string("extension", 8)->nullable(true);

			$table->timestamp("created_at");
			$table->foreignId("created_by");
			$table->timestamp("updated_at");
			$table->foreignId("updated_by");
		});
	}
};
