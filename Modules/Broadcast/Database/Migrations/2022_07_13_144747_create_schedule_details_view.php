<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_144747_create_schedule_details_view.php
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        \Illuminate\Support\Facades\DB::statement(/** @lang SQL */ <<<EOF
            CREATE VIEW `schedule_details` AS
            SELECT
                `s`.`id` AS `schedule_id`,
                `r`.`approved` AS `is_approved`
            FROM `schedules` `s`
                 LEFT JOIN `schedule_reviews` `r`
                 ON `r`.`schedule_id` = `s`.`id`
            WHERE `r`.`id` = (
                SELECT MAX(`schedule_reviews`.`id`)
                FROM `schedule_reviews`
                WHERE `schedule_reviews`.`schedule_id` = `s`.`id`)
            EOF
        );
    }
};
