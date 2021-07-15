<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - linkett.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | Defines the URL to the Linkett API Server
    |
    */

    "url" => "https://portal3.linkett.com/api",

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Defines which categories of data from the Linkett API should be used to
    | count impressions
    |
    */

    "categories" => ["ssession", "rsession"],
];
