<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_11_10_115804_add_inventory_id_to_contracts.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('contracts', function (Blueprint $table) {
			$table->foreignId("inventory_id")
			      ->nullable()
			      ->default(null)
			      ->after("contract_id")
			      ->constrained("inventory_providers", "id")
			      ->cascadeOnUpdate()
			      ->nullOnDelete();
		});

		\Illuminate\Support\Facades\DB::table("contracts")
		                              ->update(["inventory_id" => 1]);
	}
};
