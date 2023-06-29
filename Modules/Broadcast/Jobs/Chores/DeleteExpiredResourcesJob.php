<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteExpiredResourcesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Chores;

use Illuminate\Support\Facades\DB;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Jobs\Creatives\DeleteCreativeJob;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;

/**
 * This job list all schedules and creatives that have no schedules in Draft/Approved/Live states
 * and proceed to remove any external representation they may have
 */
class DeleteExpiredResourcesJob extends Job {
    public function __construct() {
    }

    public function run(): mixed {
        // This query list all the schedules that have expired but still have non-trashed external representations
        $expiredSchedules = DB::select(<<<SQL
            SELECT
              `s`.*
            FROM
              `schedules` `s`
              JOIN `schedule_details` `sd` ON `sd`.`schedule_id` = `s`.`id`
            WHERE
              `s`.`end_date` < DATE_SUB(DATE(NOW()), INTERVAL 3 DAY)
              AND EXISTS(
                SELECT *
                  FROM `external_resources` `er`
                 WHERE `er`.`resource_id` = `s`.`id`
                   AND `er`.`deleted_at` IS NULL
              )
        SQL
        );

        foreach ($expiredSchedules as $schedule) {
            DeleteScheduleJob::dispatch($schedule->id);
        }

        // This query list all the creatives that have no active scheduling (No draft, and no approved schedules whose end date is in the future), but who have non-trashed external representations
        $unusedCreatives = DB::select(<<<EOF
        SELECT `cr`.*
         FROM `creatives` `cr`
         JOIN `contents` `co` ON `co`.`id` = `cr`.`content_id`
        WHERE NOT EXISTS (
          SELECT 1
          FROM `schedules` `s`
          JOIN `schedule_details` `s2` ON `s2`.`schedule_id` = `s`.`id`
          JOIN `schedule_contents` `sc` ON `sc`.`schedule_id` = `s`.`id`
          WHERE
            `sc`.`content_id` =  `co`.`id`
          AND `s`.`end_date` > NOW()
          AND (
                `s2`.`is_approved` = 1
              OR (`s2`.`is_approved` = 0 AND `s2`.`is_rejected` = 0)
            )
          AND `s`.`deleted_at` IS NULL
          )
        AND EXISTS(
          SELECT *
          FROM `external_resources` `er`
          WHERE `er`.`resource_id` = `cr`.`id`
          AND `er`.`deleted_at` IS NULL
          )
        ORDER BY `created_at` DESC
        EOF
        );

        foreach ($unusedCreatives as $creative) {
            DeleteCreativeJob::dispatch($creative->id);
        }

        return null;
    }
}
