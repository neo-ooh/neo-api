<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsWarningsViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsWarningsViewSeeder extends Seeder {
    public function run() {
        $viewName = "products_warnings";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `p`.`id`        AS `product_id`,
           IF(`pc`.`type` = 'DIGITAL' AND (SELECT COUNT(1) FROM `products_locations` `pl` WHERE `pl`.`product_id` = `p`.`id`) = 0,
              TRUE, FALSE) AS `missing_locations`
        FROM `products` `p`
           JOIN `products_categories` `pc` ON `p`.`category_id` = `pc`.`id`
        EOS
        );
    }
}
