<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlightsViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractFlightsViewSeeder extends Seeder {
    public function run() {
        $viewName = "contracts_flights_view";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `cf`.*,
               COALESCE(SUM(DISTINCT `cl`.`impressions`), 0) AS `expected_impressions`
          FROM `contracts_flights` AS `cf`
               LEFT JOIN `contracts_lines` AS `cl` ON `cf`.`id` = `cl`.`flight_id`
               JOIN `products` AS `p` ON `cl`.`product_id` = `p`.`id`
               JOIN `products_categories` ON `p`.`category_id` = `products_categories`.`id`
          WHERE `products_categories`.`type` = 'DIGITAL'
         GROUP BY `cl`.`flight_id`
        EOS
        );
    }
}
