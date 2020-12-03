<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

return [
    "api" => [
        "url" => env("BROADSIGN_API_URL"),
        "key" => env("BROADSIGN_API_KEY"),
    ],

    "domain-id" => env("BROADSIGN_DOMAIN_ID"),
    "customer-id" => env("BROADSIGN_CUSTOMER_ID"),
    "advertising-criteria" => env("BROADSIGN_ADVERTISING_CRITERIA_ID"),

    "default-campaign-length" => 10, // years
];
