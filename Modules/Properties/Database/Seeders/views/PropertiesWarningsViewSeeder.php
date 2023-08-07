<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesWarningsViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertiesWarningsViewSeeder extends Seeder {
	public function run() {
		$viewName = "properties_warnings";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `p`.`actor_id` AS `property_id`,
           IF(
                 (SELECT COUNT(1)
                    FROM `opening_hours`
                   WHERE `opening_hours`.`property_id` = `p`.`actor_id`) < 7,
                 TRUE,
                 FALSE
             )            AS `missing_opening_hours`,
           IF(
                 (SELECT COUNT(1)
                    FROM `products`
                         JOIN `products_categories` `pc` ON
                      `products`.`category_id` = `pc`.`id`
                         LEFT JOIN `products_locations` `pl` ON
                      `products`.`id` = `pl`.`product_id`
                   WHERE `products`.`property_id` = `p`.`actor_id`
                     AND `pc`.`type` = 'DIGITAL'
                     AND `pl`.`location_id` IS NULL
                     AND `products`.`deleted_at` IS NULL) > 0,
                 TRUE,
                 FALSE
             )            AS `missing_products_locations`,
           IF(
                 `p`.`has_tenants` AND (SELECT COUNT(1)
                                          FROM `properties_tenants`
                                         WHERE `properties_tenants`.`property_id` = `p`.`actor_id`) = 0,
                 TRUE,
                 FALSE
             )            AS `missing_tenants`,
           IF(
                   `pts`.`format` <> 'daily_constant' AND (SELECT COUNT(1)
                                                             FROM `properties_traffic_monthly` `ptm`
                                                            WHERE `ptm`.`property_id` = `p`.`actor_id`
                                                              AND `ptm`.`year` = `pts`.`start_year`) < 12,
                   TRUE,
                   FALSE
             )            AS `incomplete_traffic`,
            COUNT(`dv`.`id`) as 'demographic_variables_count'
        FROM `properties` `p`
           JOIN `property_traffic_settings` `pts` ON `p`.`actor_id` = `pts`.`property_id`
           LEFT JOIN `demographic_values` `dv` ON `p`.`actor_id` = `dv`.`property_id`
        GROUP BY `p`.`actor_id`        
        EOS
		);
	}
}
