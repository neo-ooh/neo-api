<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - broadsign.php
 */

return [
    "api" => [
        "url" => env("BROADSIGN_API_URL"),
        "key" => env("BROADSIGN_API_KEY"),
    ],

    "domain-id" => env("BROADSIGN_DOMAIN_ID"),
    "customer-id" => env("BROADSIGN_CUSTOMER_ID"),

    "default-campaign-length" => 10, // years

    "advertising-criteria" => env("BROADSIGN_ADVERTISING_CRITERIA_ID"),
    "left-frame-criteria" => env("BROADSIGN_LEFT_FRAME_CRITERIA_ID"),
    "right-frame-criteria" => env("BROADSIGN_RIGHT_FRAME_CRITERIA_ID"),
];
