<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractLinesViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractLinesViewSeeder extends Seeder {
    public function run() {
        $viewName = "contracts_lines_view";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `cl`.*,
          `p2`.`network_id` as `network_id`,
          `pc`.`type` as product_type
          FROM `contracts_lines` AS `cl`
            LEFT JOIN `products` `p` ON `cl`.`product_id` = `p`.`id`
            LEFT JOIN `properties` `p2` ON `p`.`property_id` = `p2`.`actor_id`
            LEFT JOIN `products_categories` `pc` ON `p`.`category_id` = `pc`.id
        EOS
        );
    }
}
