<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsDetailsViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActorsDetailsViewSeeder extends Seeder {
	public function run() {
		$viewName = "actors_details";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `a`.`id`                      AS `id`,
               `ac`.`ancestor_id`            AS `parent_id`,
               COALESCE(`asp`.`is_group`, 0) AS `parent_is_group`,
               (SELECT EXISTS(
                           SELECT 1
                             FROM `properties`
                            WHERE `properties`.`actor_id` = `a`.`id`
                         ))                  AS `is_property`,
               (SELECT EXISTS(
                           SELECT 1
                             FROM `contracts`
                            WHERE `contracts`.`group_id` = `a`.`id`
                         ))                  AS `is_contract`
          FROM `actors` `a`
               LEFT JOIN `actors_closures` `ac`
                         ON `ac`.`descendant_id` = `a`.`id` AND `ac`.`depth` = 1
               LEFT JOIN `actors` `asp`
                         ON `asp`.`id` = `ac`.`ancestor_id`
         GROUP BY `a`.`id`,
                  `ac`.`ancestor_id`
        EOS
		);
	}
}
