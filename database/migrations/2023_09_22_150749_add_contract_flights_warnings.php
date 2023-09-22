<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_09_22_150749_add_contract_flights_warnings.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('contracts_flights', function (Blueprint $table) {
			$table->boolean("additional_lines_imported")->default(false)->after("type");
			$table->boolean("missing_lines_on_import")->default(false)->after("type");
		});
	}
};
