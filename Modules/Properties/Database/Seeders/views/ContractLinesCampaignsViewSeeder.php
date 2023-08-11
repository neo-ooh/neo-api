<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractLinesCampaignsViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractLinesCampaignsViewSeeder extends Seeder {
	public function run() {
		$viewName = "contract_lines_campaigns";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement("CREATE VIEW $viewName AS
		SELECT DISTINCT 
		`cf`.`contract_id` as 'contract_id',
		`cl`.`id` as 'contract_line_id',
		`ca`.`id` as 'campaign_id'
		FROM
		`contracts_lines` `cl`
		JOIN `contracts_flights` `cf` ON `cl`.`flight_id` = `cf`.`id`
		JOIN `campaigns` `ca` ON `ca`.`flight_id` = `cl`.`flight_id`
		JOIN `campaign_products` `cp` ON `cp`.`campaign_id` = `ca`.`id` AND `cp`.`product_id` = `cl`.`product_id`
		WHERE `ca`.`deleted_at` IS NULL"
		);
	}
}
