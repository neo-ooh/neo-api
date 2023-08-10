<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignFormatsSeeder.php
 */

namespace Neo\Modules\Broadcast\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignFormatsSeeder extends Seeder {
	public function run() {
		$viewName = "campaign_formats";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(/** @lang SQL */ " 
		CREATE VIEW $viewName AS 
		SELECT
		`campaigns`.`id` as 'campaign_id',
		`cl`.`format_id` as 'format_id'
		FROM `campaigns`
		JOIN `campaign_locations` `cl` ON `campaigns`.`id` = `cl`.`campaign_id`
		UNION DISTINCT
		SELECT `campaigns`.`id` as 'campaign_id',
		       `p`.`format_id` as 'format_id'
		FROM `campaigns`
		JOIN `campaign_products` `cp` ON `campaigns`.`id` = `cp`.`campaign_id`
		JOIN `products_view` `p` ON `cp`.`product_id` = `p`.`id`"
		);

	}
}
