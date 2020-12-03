<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateActorsDetailsView extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        DB::statement(/** @lang MySQL */ "
CREATE VIEW `actors_details` AS 
SELECT `actors`.`id`     AS `id`,
       `ac`.`ancestor_id`            AS `parent_id`,
       COALESCE(`asp`.`is_group`, 0) AS `parent_is_group`,
       COUNT(`acc`.`descendant_id`)  AS `direct_children_count`,
       COALESCE((SELECT GROUP_CONCAT(`a`.`name` ORDER BY `ac`.`depth` DESC SEPARATOR '/')
                   FROM `actors`               `a`
                        JOIN `actors_closures` `ac`
                             ON `ac`.`ancestor_id` = `a`.`id`
	                             AND `a`.`is_group` = 1
                  WHERE `ac`.`descendant_id` = `a`.`id`
                    AND `ac`.`ancestor_id` <> `a`.`id`
                  GROUP BY `ac`.`descendant_id`
                ), '')               AS `path_names`,
       COALESCE((SELECT GROUP_CONCAT(`a`.`id` ORDER BY `ac`.`depth` DESC SEPARATOR '/')
                   FROM `actors`               `a`
                        JOIN `actors_closures` `ac`
                             ON `ac`.`ancestor_id` = `a`.`id` AND `a`.`is_group` = 1
                  WHERE `ac`.`descendant_id` = `a`.`id`
                    AND `ac`.`ancestor_id` <> `a`.`id`
                  GROUP BY `ac`.`descendant_id`
                ), '')               AS `path_ids`
	FROM `actors`
	     LEFT JOIN `actors_closures` `ac`
	               ON `ac`.`descendant_id` = `actors`.`id`
		               AND `ac`.`depth` = 1
	     LEFT JOIN `actors_closures` `acc`
	               ON `acc`.`ancestor_id` = `actors`.`id`
		               AND `acc`.`depth` > 0
	     LEFT JOIN `actors`          `asp`
	               ON `asp`.`id` = `ac`.`ancestor_id`
 GROUP BY `actors`.`id`,
          `ac`.`ancestor_id`
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        DB::statement("DROP VIEW actors_details;");
    }
}
