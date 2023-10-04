<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesExtendedViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertiesExtendedViewSeeder extends Seeder {
	public function run() {
		$viewName = "properties_extended_view";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(/** @lang SQL */ <<<EOS
		CREATE VIEW $viewName AS
		SELECT `p`.*,
			`a`.name as `name`,
			COUNT(`dv`.`id`) as 'demographic_variables_count',
			(SELECT COUNT(*) FROM `inventory_pictures` WHERE `inventory_pictures`.`property_id` = `p`.`actor_id` AND `inventory_pictures`.`type` <> 'mockup') as `pictures_count`,
			(SELECT COUNT(*) FROM `inventory_pictures` WHERE `inventory_pictures`.`property_id` = `p`.`actor_id` AND `inventory_pictures`.`type` = 'mockup') as `mockups_count`
		FROM `properties` `p`
			JOIN `actors` `a` ON `p`.`actor_id` = `a`.`id`
			LEFT JOIN `demographic_values` `dv` ON `p`.`actor_id` = `dv`.`property_id`
		GROUP BY `p`.`actor_id`
		EOS
		);
	}
}
