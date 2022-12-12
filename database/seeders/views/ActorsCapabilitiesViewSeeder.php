<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsDetailsViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActorsCapabilitiesViewSeeder extends Seeder {
    public function run() {
        $viewName = "actors_all_capabilities";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<SQL
        CREATE VIEW $viewName AS
        -- Roles capabilities
        SELECT
          `ar`.`actor_id` as `actor_id`,
          `rc`.`capability_id` as `capability_id`
        FROM `actors` `a`
        JOIN `actors_roles` `ar` ON `ar`.`actor_id` = `a`.`id`
        JOIN `roles_capabilities` `rc` ON `rc`.`role_id` = `ar`.`role_id`
        -- Standalone capabilities
        UNION
        SELECT
          `ac`.`actor_id` as `actor_id`,
          `ac`.`capability_id` as `capability_id`
          FROM `actors_capabilities` `ac`
        SQL
        );
    }
}
