<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourcesDetailsViewSeeder.php
 */

namespace Neo\Modules\Broadcast\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BroadcastResourcesDetailsViewSeeder extends Seeder {
    public function run() {
        $viewName = "broadcast_resources_details";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `br`.*,
               CASE
                 WHEN `br`.`type` = 'campaign' THEN CONCAT(`ca`.`name`, ' | ', `c`.`name`)
                 WHEN `br`.`type` = 'schedule' THEN CONCAT(`sca`.`name`, ' | ', `scc`.`name`)
                 WHEN `br`.`type` = 'content' THEN `co`.`name`
                 WHEN `br`.`type` = 'creative' THEN CONCAT(`ccl`.`name`, ' | ', `cr`.`original_name`)
                 END as `name`,
               CASE
                 WHEN `br`.`type` = 'campaign' THEN `c`.`deleted_at`
                 WHEN `br`.`type` = 'schedule' THEN `s`.`deleted_at`
                 WHEN `br`.`type` = 'content' THEN `co`.`deleted_at`
                 WHEN `br`.`type` = 'creative' THEN `cr`.`deleted_at`
                 END as `deleted_at`,
               CASE
                 WHEN `br`.`type` = 'campaign' THEN `c`.id
                 WHEN `br`.`type` = 'schedule' THEN `s`.`campaign_id`
                 WHEN `br`.`type` = 'content' THEN `co`.`library_id`
                 WHEN `br`.`type` = 'creative' THEN `cr`.`content_id`
                 END as `access_id`
          FROM `broadcast_resources` `br`
               -- Campaigns joins
               LEFT JOIN `campaigns` `c` ON `br`.`id` = `c`.`id`
               LEFT JOIN `actors` `ca` ON `c`.`parent_id` = `ca`.`id`
               -- Schedules joins
               LEFT JOIN `schedules` `s` ON `br`.`id` = `s`.`id`
               LEFT JOIN `campaigns` `sca` ON `s`.`campaign_id` = `sca`.`id`
               LEFT JOIN `schedule_contents` `sc` ON `s`.`id` = `sc`.`schedule_id`
               LEFT JOIN `contents` `scc` ON `sc`.`content_id` = `scc`.`id`
               -- Contents joins
               LEFT JOIN `contents` `co` ON `br`.`id` = `co`.`id`
               -- Creatives joins
               LEFT JOIN `creatives` `cr` ON `br`.`id` = `cr`.`id`
               LEFT JOIN `contents` `cc` ON `cr`.`content_id` = `cc`.`id`
               LEFT JOIN `libraries` `ccl` ON `cc`.`library_id` = `ccl`.`id`
        EOS
        );

    }
}
