<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - filesystems.php
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver'     => 'local',
            'root'       => storage_path('app'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'public' => [
            'driver'     => 's3',
            'key'        => env('DO_SPACE_KEY'),
            'secret'     => env('DO_SPACE_SECRET'),
            'endpoint'   => env('DO_SPACE_ENDPOINT'),
            'region'     => env('DO_SPACE_REGION'),
            'bucket'     => env('DO_SPACE_BUCKET'),
            'url'        => env('DO_SPACE_URL'),
            'root'       => env('DO_SPACE_ROOT'),
            'visibility' => 'public',
        ],

        'root' => [
            'driver'     => 's3',
            'key'        => env('DO_SPACE_KEY'),
            'secret'     => env('DO_SPACE_SECRET'),
            'endpoint'   => env('DO_SPACE_ENDPOINT'),
            'region'     => env('DO_SPACE_REGION'),
            'bucket'     => env('DO_SPACE_BUCKET'),
            'url'        => env('DO_SPACE_URL'),
            'root'       => "/",
            'visibility' => 'public',
        ],

        'dev' => [
            'driver'     => 's3',
            'key'        => env('DO_SPACE_KEY'),
            'secret'     => env('DO_SPACE_SECRET'),
            'endpoint'   => env('DO_SPACE_ENDPOINT'),
            'region'     => env('DO_SPACE_REGION'),
            'bucket'     => env('DO_SPACE_BUCKET'),
            'url'        => env('DO_SPACE_URL'),
            'root'       => "/ooh-dev",
            'visibility' => 'public',
        ],

        'canadian-press' => [
            'driver'   => 'ftp',
            'host'     => env('CANADIAN_PRESS_URL'),
            'username' => env('CANADIAN_PRESS_USERNAME'),
            'password' => env('CANADIAN_PRESS_PASSWORD'),
            'root'     => env('CANADIAN_PRESS_FTP_ROOT'),
            'port'     => 21,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
