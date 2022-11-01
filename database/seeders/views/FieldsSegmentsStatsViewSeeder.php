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
        SELECT `s`.`id`                              AS `schedule_id`,
               `c`.`is_approved` || COALESCE(`r`.`approved`, 0) AS `is_approved`
        FROM `schedules` `s`
          JOIN `contents` `c` ON `c`.`id` = `s`.`content_id`
          LEFT JOIN `schedule_reviews` `r`
            ON `r`.`id` = (
            SELECT MAX(`schedule_reviews`.`id`)
            FROM `schedule_reviews`
            WHERE `schedule_reviews`.`schedule_id` = `s`.`id`)
        EOS
        );

    }
}
