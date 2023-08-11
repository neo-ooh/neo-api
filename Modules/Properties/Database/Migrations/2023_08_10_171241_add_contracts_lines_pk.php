<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_08_10_171241_add_contracts_lines_pk.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('contracts_lines', function (Blueprint $table) {
			$table->dropPrimary();
		});

		Schema::table('contracts_lines', function (Blueprint $table) {
			$table->id()->first();
		});
	}
};
