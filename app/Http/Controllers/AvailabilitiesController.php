<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AvailabilitiesController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\ListAvailabilitiesRequest;

class AvailabilitiesController {
    public function index(ListAvailabilitiesRequest $request) {
        $productIds = collect($request->input("product_ids"));

        $dates = collect(CarbonPeriod::create($request->input("from"), $request->input("to"))
                                     ->toArray())->map(fn(Carbon $d) => ["d" => $d->toDateString()]);

        // Start by creating a temporary table, and fill it with our dates
        DB::statement("DROP TABLE IF EXISTS `avail_dates`", []);
        DB::statement("CREATE TEMPORARY TABLE `avail_dates` (`d` date)", []);

        DB::table("avail_dates")->insert($dates->toArray());

        $locale = $request->input("locale", "en-CA");

        // Run our query to fetch availabilities
        // Since we use bindings, we need to prepare a specific list of `?` to be replace with the product ids
        $productBindings = $productIds->map(fn() => "?")->join(',');

        $availabilities = DB::select(<<<EOS
            SELECT `p`.`id`                                                              AS `product_id`,
                   `d`.`d`                                                               AS `date`,
                   CAST(COALESCE(`lc`.`free_spots_count`, 1) AS unsigned)                AS `reservable_spots_count`,
                   COALESCE(SUM(`cl`.`spots`), 0)                                        AS `reserved_spots_count`,
                   COALESCE(`lc`.`free_spots_count`, 1) - COALESCE(SUM(`cl`.`spots`), 0) AS `free_spots_count`,
                   COUNT(`u`.`id`) > 0                                                   AS `unavailable`,
                   `ut`.`reason`                                                         AS `unavailability_reason`
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
                   LEFT JOIN `unavailabilities_translations` `ut` ON `u`.`id` = `ut`.`unavailability_id` AND `ut`.`locale` = ?
             WHERE `p`.`id` IN ($productBindings)
             GROUP BY `p`.`id`, `d`.`d`
            EOS
            , [$locale, ...$productIds->toArray()]);

        return new Response($availabilities);
    }
}
