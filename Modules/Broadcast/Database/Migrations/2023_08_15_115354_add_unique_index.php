<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_08_15_115354_add_unique_index.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::rename('campaign_products', 'campaign_products_old');
		Schema::table("campaign_products_old", function (Blueprint $table) {
			$table->dropForeign("campaign_products_campaign_id_foreign");
			$table->dropForeign("campaign_products_product_id_foreign");
		});

		Schema::create('campaign_products', function (Blueprint $table) {
			$table->foreignId("campaign_id")->constrained("campaigns", "id")->cascadeOnUpdate()->cascadeOnDelete();
			$table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();

			$table->timestamps();
		});

		Schema::table('campaign_products', function (Blueprint $table) {
			$table->unique(["campaign_id", "product_id"]);
		});

		\Illuminate\Support\Facades\DB::statement("
			INSERT IGNORE INTO `campaign_products` SELECT * FROM `campaign_products_old`
		");

		Schema::drop("campaign_products_old");
	}
};
