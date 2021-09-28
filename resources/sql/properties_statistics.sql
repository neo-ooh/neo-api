/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - properties_statistics.sql
 */

-- ------------------------------------
-- Markets
-- Traffic for specific property per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
 WHERE `pt`.`property_id` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for market for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
 WHERE `c`.`market_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for province for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
 WHERE `c`.`province_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;



-- ------------------------------------
-- Products

-- Total traffic for properties in market with specific product for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
           JOIN `odoo_properties` `op` ON `op`.`property_id` = `p`.`actor_id`
           JOIN `odoo_properties_products` `oppc` ON `oppc`.`property_id` = `op`.`property_id`
 WHERE `c`.`market_id` = ?
   AND `oppc`.`product_category_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for properties in province with specific product for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
           JOIN `odoo_properties` `op` ON `op`.`property_id` = `p`.`actor_id`
           JOIN `odoo_properties_products` `oppc` ON `oppc`.`property_id` = `op`.`property_id`
 WHERE `c`.`province_id` = ?
   AND `oppc`.`product_category_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for properties with specific product for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `odoo_properties` `op` ON `op`.`property_id` = `p`.`actor_id`
           JOIN `odoo_properties_products` `oppc` ON `oppc`.`property_id` = `op`.`property_id`
 WHERE `oppc`.`product_category_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- ------------------------------------
-- Network

-- Total traffic for properties in specific network in specific market for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
 WHERE `c`.`market_id` = ?
   AND `p`.`network_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for properties in specific network in specific province for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
           JOIN `addresses` `addr` ON `addr`.`id` = `p`.`address_id`
           JOIN `cities` `c` ON `c`.`id` = `addr`.`city_id`
 WHERE `c`.`province_id` = ?
   AND `p`.`network_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- Total traffic for properties in specific network for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `properties` `p` ON `p`.`actor_id` = `pt`.`property_id`
 WHERE `p`.`network_id` = ?
   AND `pt`.`year` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;

-- ------------------------------------
-- Default

-- Total traffic for properties children of specified property for specific year per month
SELECT `pt`.`year`,
       `pt`.`month`,
       SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`
  FROM `properties_traffic` `pt`
           JOIN `actors_closures` `ac` ON `ac`.`descendant_id` = `pt`.`property_id` AND `ac`.`depth` > 0
 WHERE `pt`.`year` = ?
   AND `ac`.`ancestor_id` = ?
 GROUP BY `pt`.`year`, `pt`.`month`;
