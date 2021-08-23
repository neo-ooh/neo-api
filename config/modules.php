<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - modules.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    | Connect's functionalities are divided into modules. Modules can be
    | activated or deactivated, and have options specific to each of them.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Core Modules
    |--------------------------------------------------------------------------
    |
    | Core modules cannot be deactivated, they are required for Connect to work.
    |
    */
    "core" => [

    ],


    /*
    |--------------------------------------------------------------------------
    | Broadcasters Modules
    |--------------------------------------------------------------------------
    |
    | These modules provided connectivity with broadcasting (DOOH) systems.
    | If all of these modules are deactivated, Content scheduled on Connect
    | will not be shown anywhere
    |
    */
    "broadsign" => [
        "enabled" => env('MODULE_BROADSIGN_ENABLED', true),

        "api-url" => env("BROADSIGN_API_URL", "https://api.broadsign.com:10889/rest"),

        "default-campaign-length" => 10, // years
        "bursts" => [
            "default-quality" => 80 // %
        ]
    ],
    "pisignage" => [
        "enabled" => env('MODULE_PISIGNAGE_ENABLED', true),
    ],


    /*
    |--------------------------------------------------------------------------
    | Access Tokens Module
    |--------------------------------------------------------------------------
    |
    | Allows access to Connect's API through the use of access-tokens.
    | This allows for third-parties services to interact with Connect.
    |
    */
    "access-tokens" => [
        "enabled" => env('MODULE_ACCESS_TOKENS_ENABLED', true),
    ],

    "documents" => [
        "enabled" => env('MODULE_DOCUMENTS_ENABLED', true),
    ],


    /*
    |--------------------------------------------------------------------------
    | Dynamics Module
    |--------------------------------------------------------------------------
    |
    | Provide the Weather and News Dynamics, with external data-fetching and
    | customization interface
    |
    */
    "dynamics" => [
        "enabled" => env('MODULE_DYNAMICS_ENABLED', true),
    ],

    "inventory" => [
        "enabled" => env('MODULE_INVENTORY_ENABLED', true),
    ],



    /*
    |--------------------------------------------------------------------------
    | Quality of Life Modules
    |--------------------------------------------------------------------------
    |
    | These modules provide useful features related to the usage of Connect,
    | but have no impact on the rest of the platform.
    |
    */
    "brandings" => [
        "enabled" => env('MODULE_BRANDINGS_ENABLED', true),
    ],
    "headlines" => [
        "enabled" => env('MODULE_HEADLINES_ENABLED', true),
    ],
    "review-templates" => [
        "enabled" => env('MODULE_REVIEW_TEMPLATES_ENABLED', true),
    ],


    /*
    |--------------------------------------------------------------------------
    | Neo-OOH specific modules
    |--------------------------------------------------------------------------
    |
    | These modules are built with features nor logic that are specific to
    | Neo-ooh.
    |
    */
    "contracts" => [
        "enabled" => env('MODULE_CONTRACTS_ENABLED', true),
    ],
    "properties" => [
        "enabled" => env('MODULE_PROPERTIES_ENABLED', true),

        "linkett" => [
            "enabled" => true,
            /*
            |----------------------------------------------------------------
            | API URL
            |----------------------------------------------------------------
            |
            | Defines the URL to the Linkett API Server
            |
            */

            "url" => "https://portal3.linkett.com/api",

            /*
            |----------------------------------------------------------------
            | Categories
            |----------------------------------------------------------------
            |
            | Defines which categories of data from the Linkett API should be used to
            | count impressions
            |
            */

            "categories" => ["ssession", "rsession"],
        ]
    ],

    "impressions" => [
        "enabled" => env('MODULE_IMPRESSIONS_ENABLED', true),
    ],
];
