<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_11_07_151715_create_mobile_product_brackets_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('mobile_product_brackets', function (Blueprint $table) {
			$table->id();

			$table->foreignId("mobile_product_id")->constrained("mobile_products", "id")->cascadeOnUpdate()->cascadeOnDelete();
			$table->unsignedInteger("budget_min")->default(0)->nullable(false);
			$table->unsignedInteger("budget_max")->nullable()->default(true);
			$table->unsignedBigInteger("impressions_min")->nullable(false);
			$table->double("cpm");

			$table->timestamps();
		});
	}
};
