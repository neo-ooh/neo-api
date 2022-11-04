<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldsSegmentsStatsViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FieldsSegmentsStatsViewSeeder extends Seeder {
    public function run() {
        $viewName = "fields_segments_stats";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW `$viewName` AS
          SELECT `fs`.`id` AS `id`,
                 COUNT(`pfsv`.`property_id`) AS `value_count`,
                 MIN(`pfsv`.`index`) AS `min_index`,
                 MAX(`pfsv`.`index`) AS `max_index`
        FROM `fields_segments` `fs`
          LEFT JOIN `properties_fields_segments_values` `pfsv`
            ON `pfsv`.`fields_segment_id` = `fs`.`id`
        GROUP BY
            `fs`.`id`
        EOS
        );

    }
}
