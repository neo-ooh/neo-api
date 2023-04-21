<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SchedulesDetailsViewSeeder.php
 */

namespace Neo\Modules\Broadcast\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchedulesDetailsViewSeeder extends Seeder {
    public function run() {
        $viewName = "schedule_details";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `s`.`id`                                               AS `schedule_id`,
               CASE
                 WHEN NOT EXISTS(
                     SELECT *
                       FROM `contents` `c`
                      WHERE `c`.`id` IN (SELECT `content_id`
                        FROM `schedule_contents` `sc`
                       WHERE `sc`.`schedule_id` = `s`.`id`)
                        AND `c`.`is_approved` = FALSE
                   ) THEN TRUE
                 ELSE FALSE
                 END OR COALESCE(`r`.`approved`, 0)                  AS `is_approved`,
               NOT CASE
                     WHEN NOT EXISTS(
                         SELECT *
                           FROM `contents` `c`
                          WHERE `c`.`id` IN (SELECT `content_id`
                            FROM `schedule_contents` `sc`
                           WHERE `sc`.`schedule_id` = `s`.`id`)
                            AND `c`.`is_approved` = FALSE
                       ) THEN TRUE
                     ELSE FALSE
                 END AND COALESCE(`r`.`approved`, 1) = 0 AS `is_rejected`
          FROM `schedules` `s`
               LEFT JOIN `schedule_reviews` `r`
                         ON `r`.`id` = (SELECT MAX(`schedule_reviews`.`id`)
                                          FROM `schedule_reviews`
                                         WHERE `schedule_reviews`.`schedule_id` = `s`.`id`)

        EOS
        );

    }
}
