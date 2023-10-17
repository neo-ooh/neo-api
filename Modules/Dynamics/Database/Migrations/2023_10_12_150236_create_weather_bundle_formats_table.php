<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_10_12_150236_create_weather_bundle_formats_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('weather_bundle_formats', function (Blueprint $table) {
			$table->foreignId("bundle_id")->constrained("weather_bundles", "id")->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->cascadeOnDelete();
		});
	}
};
