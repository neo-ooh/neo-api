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

    "domain-id" => (int)env("BROADSIGN_DOMAIN_ID"),
    "customer-id" => (int)env("BROADSIGN_CUSTOMER_ID"),

    "default-campaign-length" => 10, // years

    "category-separation-id" => (int)env("BROADSIGN_CATEGORY_SEPARATION_ID"),

    "advertising-criteria" => (int)env("BROADSIGN_ADVERTISING_CRITERIA_ID"),
    "left-frame-criteria" => (int)env("BROADSIGN_LEFT_FRAME_CRITERIA_ID"),
    "right-frame-criteria" => (int)env("BROADSIGN_RIGHT_FRAME_CRITERIA_ID"),
];
