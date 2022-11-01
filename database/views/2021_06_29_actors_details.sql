/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_06_29_actors_details.sql
 */

CREATE VIEW `actors_details` AS
SELECT `a`.`id`                      AS `id`,
       `ac`.`ancestor_id`            AS `parent_id`,
       COALESCE(`asp`.`is_group`, 0) AS `parent_is_group`,
       COUNT(`acc`.`descendant_id`)  AS `direct_children_count`,
       (SELECT EXISTS(
                   SELECT 1
                     FROM `neo_ooh`.`properties`
                    WHERE `neo_ooh`.`properties`.`actor_id` = `a`.`id`
                 ))                  AS `is_property`,
       COALESCE(
           (SELECT GROUP_CONCAT(
                       `apn`.`name`
                       ORDER BY
                       `ac`.`depth`
                       DESC SEPARATOR
                       '/'
                     )
              FROM `neo_ooh`.`actors` `apn`
                   JOIN `neo_ooh`.`actors_closures` `ac`
                        ON `ac`.`ancestor_id` = `apn`.`id` AND `ac`.`depth` > 0
             WHERE `ac`.`descendant_id` = `a`.`id`
               AND `apn`.`is_group` = 1
             GROUP BY `ac`.`descendant_id`),
           ''
         )                           AS `path_names`,
       COALESCE(
           (SELECT GROUP_CONCAT(
                       `apn`.`id`
                       ORDER BY
                       `ac`.`depth`
                       DESC SEPARATOR
                       '/'
                     )
              FROM `neo_ooh`.`actors` `apn`
                   JOIN `neo_ooh`.`actors_closures` `ac` ON `ac`.`ancestor_id` = `apn`.`id` AND `ac`.`depth` > 0
             WHERE `ac`.`descendant_id` = `a`.`id`
               AND `apn`.`is_group` = 1
             GROUP BY `ac`.`descendant_id`),
           ''
         )                           AS `path_ids`
  FROM `neo_ooh`.`actors` `a`
       LEFT JOIN `neo_ooh`.`actors_closures` `ac`
                 ON `ac`.`descendant_id` = `a`.`id` AND `ac`.`depth` = 1
       LEFT JOIN `neo_ooh`.`actors_closures` `acc`
                 ON `acc`.`ancestor_id` = `a`.`id` AND `acc`.`depth` = 1
       LEFT JOIN `neo_ooh`.`actors` `asp`
                 ON `asp`.`id` = `ac`.`ancestor_id`
 GROUP BY `a`.`id`,
          `ac`.`ancestor_id`
