<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_08_10_133505_create_campaign_products_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('campaign_products', function (Blueprint $table) {
			$table->foreignId("campaign_id")->constrained("campaigns", "id")->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();

			$table->timestamps();
		});
	}
};
