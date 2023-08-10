<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_08_10_133653_migrate_campaigns_products.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		\Illuminate\Support\Facades\DB::statement(<<<EOF
				INSERT INTO `campaign_products`
				SELECT DISTINCT `cl`.`campaign_id` AS "campaign_id",
					   `cl`.`product_id` AS "location_id",
					   `cl`.`created_at` AS "created_at",
					   `cl`.`updated_at` AS "updated_at"
				FROM `campaign_locations` `cl`
				WHERE `cl`.`product_id` IS NOT NULL
				EOF
		);

		\Illuminate\Support\Facades\DB::statement(<<<EOF
				DELETE FROM `campaign_locations` WHERE `product_id` IS NOT NULL
				EOF
		);

		Schema::table("campaign_locations", function (Blueprint $table) {
			$table->dropConstrainedForeignId("product_id");
		});
	}
};
