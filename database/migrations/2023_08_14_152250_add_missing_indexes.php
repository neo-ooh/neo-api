<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_08_14_152250_add_missing_indexes.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('contracts_flights', function (Blueprint $table) {
			$table->index("start_date");
			$table->index("end_date");
			$table->index("type");
		});

		Schema::table("unavailabilities", function (Blueprint $table) {
			$table->index("start_date");
			$table->index("end_date");
		});
	}
};
