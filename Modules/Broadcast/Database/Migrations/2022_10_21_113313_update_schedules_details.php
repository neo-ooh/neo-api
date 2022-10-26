<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_10_21_113313_update_schedules_details.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSchedulesDetails extends Migration {
    public function up() {
        Schema::table('', static function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement(<<<EOF
            DROP VIEW IF EXISTS schedule_details
            EOF
            );
            \Illuminate\Support\Facades\DB::statement(<<<EOF
            CREATE VIEW schedule_details AS
            SELECT
                `s`.`id` AS `schedule_id`,
                `c`.`is_approved` <> 0 OR COALESCE(`r`.`approved`, 0) AS `is_approved`,
                `c`.`is_approved` = 0 AND COALESCE(`r`.`approved`, 1) = 0 AS `is_rejected`
            FROM
                (
                    (
                        `neo_ooh_dev`.`schedules` `s`
                    JOIN `neo_ooh_dev`.`contents` `c`
                    ON
                        (`c`.`id` = `s`.`content_id`)
                    )
                LEFT JOIN `neo_ooh_dev`.`schedule_reviews` `r`
                ON
                    (
                        `r`.`id` =(
                        SELECT
                            MAX(
                                `neo_ooh_dev`.`schedule_reviews`.`id`
                            )
                        FROM
                            `neo_ooh_dev`.`schedule_reviews`
                        WHERE
                            `neo_ooh_dev`.`schedule_reviews`.`schedule_id` = `s`.`id`
                    )
                    )
                )
            EOF
            );
        });
    }
}
