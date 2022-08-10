<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_08_05_114140_update_schedules_details_view.php
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        DB::statement("DROP VIEW IF EXISTS schedule_details");

        DB::statement(/** @lang SQL */ <<<EOF
            CREATE VIEW `schedule_details` AS SELECT
                `s`.`id` AS `schedule_id`,
                `r`.`approved`,
                (`c`.`is_approved` || `r`.`approved`) AS `is_approved`
            FROM
                (
                    `neo_ooh_dev`.`schedules` `s`
                LEFT JOIN `neo_ooh_dev`.`schedule_reviews` `r`
                ON
                    (`r`.`schedule_id` = `s`.`id`)
                )
                JOIN `contents` `c` ON `c`.`id` = `s`.`content_id`
            WHERE
                `r`.`id` =(
                SELECT
                    MAX(
                        `neo_ooh_dev`.`schedule_reviews`.`id`
                    )
                FROM
                    `neo_ooh_dev`.`schedule_reviews`
                WHERE
                    `neo_ooh_dev`.`schedule_reviews`.`schedule_id` = `s`.`id`
            );
            EOF
        );
    }
};
