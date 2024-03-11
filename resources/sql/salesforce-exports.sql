/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - salesforce-exports.sql
 */

-- Actors Export
# SELECT
#   a.id as 'Connect ID',
#   a.name as 'Account Name',
#   'Partner' as 'Account Type',
#   811 as `Parent Id`
# FROM `actors` `a`
# JOIN `actors_details` `ad` ON `ad`.`id` = `a`.`id`
# JOIN `actors_closures` `ac` ON `a`.id = `ac`.`descendant_id`
# WHERE `ac`.`ancestor_id` = 811
# AND `ac`.`depth` = 1
# AND `a`.`is_group` IS TRUE
# AND `ad`.`is_property` IS FALSE
# AND `a`.`deleted_at` IS NULL

-- Properties Export
# SELECT
#   pv.`actor_id` as 'Connect ID',
#   pv.name as 'Account Name',
# #   COALESCE(pt.`name_en`, 'Other') as 'Type',
#   'Gas Station' as 'Type',
#   addr.`line_1` as 'Shipping Street',
#   `cities`.`name` as 'Shipping City',
#   `addr`.`zipcode` as 'Shipping Zip/Postal Code',
#   `provinces`.`slug` as 'Shipping State/Province',
#   'CA' as `Shipping Country`,
#   `ad`.`parent_id` as 'Parent'
# #   823 as 'Parent'
# FROM `properties_view` `pv`
# JOIN `actors_details` `ad` ON ad.`id` = `pv`.`actor_id`
# JOIN `actors_closures` `ac` ON `pv`.`actor_id` = `ac`.`descendant_id`
# LEFT JOIN `property_types` `pt` ON pt.`id` = pv.`type_id`
# LEFT JOIN `addresses` `addr` ON `pv`.`address_id` = `addr`.`id`
# LEFT JOIN `cities` ON `addr`.`city_id` = `cities`.`id`
# LEFT JOIN `provinces` ON `cities`.`province_id` = `provinces`.`id`
# WHERE pv.`deleted_at` IS NULL
# AND `ac`.`ancestor_id` = 811
# AND (`ac`.`depth` = 1 OR `ac`.`depth` = 2);


-- Users Export
# SELECT
#   `a`.`id` as 'Connect ID',
#   SUBSTRING_INDEX(`a`.name, ' ', 1) as 'First Name',
#   SUBSTRING(`a`.name, LENGTH(SUBSTRING_INDEX(`a`.name, ' ', 1)) + 2) as 'Last Name',
#   `a`.`email` as Email,
#   `phones`.`number` as `Phone`,
#   `ad`.`parent_id` as 'Account Name'
# FROM `actors` `a`
#   JOIN `actors_details` `ad` ON ad.`id` = `a`.`id`
#   JOIN `actors_closures` `ac` ON `a`.`id` = `ac`.`descendant_id`
#   LEFT JOIN `phones` ON `a`.`phone_id` = `phones`.`id`
# WHERE a.`deleted_at` IS NULL
#   AND a.`is_group` IS FALSE
#   AND `ac`.`ancestor_id` IN(10)
# AND `ac`.`depth` = 1

-- Products Export
SELECT
  `pv`.id as 'Connect ID',
  `p`.`actor_id` as `Account ID`,
  `pv`.`name_en` as 'Asset Name',
  `pc`.`type` as 'Product Type',
  `pv`.`quantity` as 'Quantity',
  'Installed' as 'Status',
  CONCAT(`p`.`actor_id`, '-', `pv`.id) as 'Serial Number'
FROM `products_view` `pv`
JOIN `actors_closures` `ac` ON ac.`descendant_id` = `pv`.`property_id`
JOIN `products_categories` `pc` ON pc.`id` = pv.`category_id`
JOIN `properties` `p` ON p.`actor_id` = pv.`property_id`
WHERE `pv`.`deleted_at` IS NULL
AND `is_bonus` = 0
AND ac.`ancestor_id` = 811
AND p.`deleted_at` IS NULL
ORDER BY `pv`.`id`
