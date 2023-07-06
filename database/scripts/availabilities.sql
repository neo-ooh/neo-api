/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - availabilities.sql
 */

DROP TABLE IF EXISTS `avail_dates`;
CREATE TEMPORARY TABLE `avail_dates`
(
  `d` date
);

# INSERT INTO `avail_dates` VALUE ('2023-07-01'), ('2023-11-15'), ('2023-07-02'), ('2023-07-03'), ('2023-07-04'), ('2023-07-05'), ('2023-07-06'), ('2023-07-07'), ('2023-07-08'), ('2023-07-09'), ('2023-07-10'), ('2023-07-11'), ('2023-07-12');
INSERT INTO `avail_dates` VALUE ('2023-10-01'),('2023-10-02'),('2023-10-03'),('2023-10-04'),('2023-10-05'),('2023-10-06'),('2023-10-07'),('2023-10-08'),('2023-10-09'),('2023-10-10'),('2023-10-11'),('2023-10-12'),('2023-10-13'),('2023-10-14'),('2023-10-15'),('2023-10-16'),('2023-10-17'),('2023-10-18'),('2023-10-19'),('2023-10-20'),('2023-10-21'),('2023-10-22'),('2023-10-23'),('2023-10-24'),('2023-10-25'),('2023-10-26'),('2023-10-27'),('2023-10-28'),('2023-10-29'),('2023-10-30'),('2023-10-31'),('2023-11-01'),('2023-11-02'),('2023-11-03'),('2023-11-04'),('2023-11-05'),('2023-11-06'),('2023-11-07'),('2023-11-08'),('2023-11-09'),('2023-11-10'),('2023-11-11'),('2023-11-12'),('2023-11-13'),('2023-11-14'),('2023-11-15'),('2023-11-16'),('2023-11-17'),('2023-11-18'),('2023-11-19'),('2023-11-20'),('2023-11-21'),('2023-11-22'),('2023-11-23'),('2023-11-24'),('2023-11-25'),('2023-11-26'),('2023-11-27'),('2023-11-28'),('2023-11-29'),('2023-11-30'),('2023-12-01');

# This query returns one line for every product for every date in the `avail_dates` temporary table, specifying how many spots can be booked on this product
# on each day, how many are already booked, how many are left available, as well as a flag to tell if the product is actually available on that day
# This uses the loop configuration of the format associated with the products. For static products without a format, an availability of one is used as a default.

SELECT `p`.`id`                                                              AS `product_id`,
       `d`.`d`                                                               AS `date`,
       CAST(COALESCE(`lc`.`free_spots_count`, 1) AS unsigned)                AS `reservable_spots_count`,
       COALESCE(SUM(`cl`.`spots`), 0)                                        AS `reserved_spots_count`,
       COALESCE(`lc`.`free_spots_count`, 1) - COALESCE(SUM(`cl`.`spots`), 0) AS `free_spots_count`,
       COUNT(`u`.`id`) > 0                                                   AS `unavailable`
  FROM `products` `p`
       CROSS JOIN `avail_dates` `d`
       JOIN `products_categories` `pc` ON `p`.`category_id` = `pc`.`id`
       LEFT JOIN (SELECT `cl`.*, `cf`.`start_date` `start_date`, `cf`.`end_date` `end_date`
                    FROM `contracts_lines` `cl`
                         JOIN `contracts_flights` `cf` ON `cl`.`flight_id` = `cf`.`id`
                   WHERE `cf`.`type` IN ('guaranteed', 'bonus')) `cl`
                 ON `cl`.`product_id` = `p`.`id` AND `d`.`d` BETWEEN `cl`.`start_date` AND `cl`.`end_date`
       LEFT JOIN `formats` `f` ON `f`.`id` = COALESCE(`p`.`format_id`, `pc`.`format_id`)
       LEFT JOIN `format_loop_configurations` `flc` ON `f`.`id` = `flc`.`format_id`
       LEFT JOIN `loop_configurations` `lc` ON `flc`.`loop_configuration_id` = `lc`.`id`
    AND DATE_FORMAT(`d`.`d`, "%m-%d") BETWEEN DATE_FORMAT(`lc`.`start_date`, "%m-%d") AND DATE_FORMAT(`lc`.`end_date`, "%m-%d")
       LEFT JOIN `products_unavailabilities` `pu` ON `p`.`id` = `pu`.`product_id`
       LEFT JOIN `properties_unavailabilities` `pru` ON `pru`.`property_id` = `p`.`property_id`
       LEFT JOIN `unavailabilities` `u` ON (`pu`.`unavailability_id` = `u`.`id` OR `pru`.`unavailability_id` = `u`.`id`)
    AND ((`u`.`start_date` IS NOT NULL AND `u`.`end_date` IS NOT NULL AND
          `d`.`d` BETWEEN `u`.`start_date` AND `u`.`end_date`)
      OR (`u`.`start_date` IS NOT NULL AND `u`.`end_date` IS NULL AND `u`.`start_date` <= `d`.`d`)
      OR (`u`.`start_date` IS NULL AND `u`.`end_date` IS NOT NULL AND `u`.`end_date` >= `d`.`d`))
 WHERE `p`.`id` IN (7524)
 GROUP BY `p`.`id`, `d`.`d`;

/*SELECT `p`.`id`                                                              AS `product_id`,
       `d`.`d`                                                               AS `date`,
       CAST(COALESCE(`lc`.`free_spots_count`, 1) AS unsigned)                AS `reservable_spots_count`,
       COALESCE(SUM(`cl`.`spots`), 0)                                        AS `reserved_spots_count`,
       COALESCE(`lc`.`free_spots_count`, 1) - COALESCE(SUM(`cl`.`spots`), 0) AS `free_spots_count`,
       (SELECT COUNT(`u`.`id`) > 0
          FROM `products` `p`
               LEFT JOIN `products_unavailabilities` `pu` ON `p`.`id` = `pu`.`product_id`
               LEFT JOIN `properties_unavailabilities` `pru` ON `pru`.`property_id` = `p`.`property_id`
               LEFT JOIN `unavailabilities` `u` ON `pu`.`unavailability_id` = `u`.`id` OR `pru`.`unavailability_id` = `u`.`id`
         WHERE `p`.`id` = `cl`.`product_id`
           AND ((`u`.`start_date` IS NOT NULL AND `u`.`end_date` IS NOT NULL AND
                 `d`.`d` BETWEEN `u`.`start_date` AND `u`.`end_date`)
           OR (`u`.`start_date` IS NOT NULL AND `u`.`end_date` IS NULL AND `u`.`start_date` <= `d`.`d`)
           OR (`u`.`start_date` IS NULL AND `u`.`end_date` IS NOT NULL AND `u`.`end_date` >= `d`.`d`)
           ))                                                                AS `unavailable`
  FROM `products` `p`
       CROSS JOIN `avail_dates` `d`
       JOIN `products_categories` `pc` ON `p`.`category_id` = `pc`.`id`
       LEFT JOIN (SELECT `cl`.*, `cf`.`start_date` `start_date`, `cf`.`end_date` `end_date`
                    FROM `contracts_lines` `cl`
                         JOIN `contracts_flights` `cf` ON `cl`.`flight_id` = `cf`.`id`
                   WHERE `cf`.`type` IN ('guaranteed', 'bonus')) `cl`
                 ON `cl`.`product_id` = `p`.`id` AND `d`.`d` BETWEEN `cl`.`start_date` AND `cl`.`end_date`
       LEFT JOIN `formats` `f` ON `f`.`id` = COALESCE(`p`.`format_id`, `pc`.`format_id`)
       LEFT JOIN `format_loop_configurations` `flc` ON `f`.`id` = `flc`.`format_id`
       LEFT JOIN `loop_configurations` `lc` ON `flc`.`loop_configuration_id` = `lc`.`id`
    AND DATE_FORMAT(`d`.`d`, "%m-%d") BETWEEN DATE_FORMAT(`lc`.`start_date`, "%m-%d") AND DATE_FORMAT(`lc`.`end_date`, "%m-%d")
 WHERE `p`.`id` IN (7524)
 GROUP BY `p`.`id`, `d`.`d`;*/
