<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - services.php
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    "meteo-media" => [
        "endpoint"        => env("METEO_MEDIA_ENDPOINT", "http://wx.api.pelmorex.com/weather"),
        "key"             => env("METEO_MEDIA_API_KEY"),
        "record-lifespan" => env("METEO_MEDIA_RECORD_LIFESPAN", 2700),
    ],

    "canadian-press" => [
        "disk"    => "canadian-press",
        "storage" => [
            "path" => "dynamics/news/medias/"
        ]
    ],


    "google" => [
        "key" => env('GOOGLE_MAPS_API_KEY'),
    ],


    "foursquare" => [
        "key" => env('FOURSQUARE_API_KEY'),
    ],

];
