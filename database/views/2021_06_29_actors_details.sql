CREATE VIEW `actors_details` AS
SELECT `a`.`id`                                       AS `id`,
       `ac`.`ancestor_id`                             AS `parent_id`,
       CASE
           WHEN `asp`.`is_group` IS NOT NULL THEN `asp`.`is_group`
           ELSE 0
           END                                        AS `parent_is_group`,
       COUNT(`acc`.`descendant_id`)                   AS `direct_children_count`,
        (SELECT EXISTS(SELECT 1 FROM `properties` WHERE `properties`.`actor_id` = `a`.`id`)) AS `is_property`,
       COALESCE((SELECT GROUP_CONCAT(`apn`.`name` ORDER BY `ac`.`depth` DESC SEPARATOR '/')
                   FROM (`actors` `apn`
                            JOIN `actors_closures` `ac` ON (`ac`.`ancestor_id` = `apn`.`id` AND `ac`.`depth` > 0))
                  WHERE `ac`.`descendant_id` = `a`.`id`
                    AND `apn`.`is_group` = 1
                  GROUP BY `ac`.`descendant_id`), '') AS `path_names`,
       COALESCE((SELECT GROUP_CONCAT(`apn`.`id` ORDER BY `ac`.`depth` DESC SEPARATOR '/')
                   FROM (`actors` `apn`
                            JOIN `actors_closures` `ac` ON (`ac`.`ancestor_id` = `apn`.`id` AND `ac`.`depth` > 0))
                  WHERE `ac`.`descendant_id` = `a`.`id`
                    AND `apn`.`is_group` = 1
                  GROUP BY `ac`.`descendant_id`), '') AS `path_ids`
  FROM (((`actors` `a` LEFT JOIN `actors_closures` `ac` ON (`ac`.`descendant_id` = `a`.`id` AND `ac`.`depth` = 1)) LEFT JOIN `actors_closures` `acc` ON (`acc`.`ancestor_id` = `a`.`id` AND `acc`.`depth` = 1))
           LEFT JOIN `actors` `asp` ON (`asp`.`id` = `ac`.`ancestor_id`))
 GROUP BY `a`.`id`, `ac`.`ancestor_id`;
