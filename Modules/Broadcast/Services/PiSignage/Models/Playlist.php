<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Playlist.php
 */

namespace Neo\Modules\Broadcast\Services\PiSignage\Models;

use Neo\Modules\Broadcast\Services\PiSignage\API\PiSignageClient;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\API\Parsers\SingleResourcesParser;

/**
 * Class Group
 *
 * @package Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string   $name
 * @property integer  $version
 * @property string   $layout
 * @property string   $templateName
 * @property array    $videoWindow = [
 *  "length" => "integer",
 *  "width" => "integer",
 *  "xoffset" => "integer",
 *  "yoffset" => "integer"
 * ]
 * @property array    $zoneVideoWindow
 * @property array    $assets      = [
 *      [
 *          "filename" => "string",
 *          "duration" => "integer",
 *          "isVideo" => "boolean",
 *          "selected" => "boolean",
 *          "option" => "array",
 *          "fullscreen" => "boolean",
 *          "side" => "string",
 *          "bottom" => "string",
 *          "zone4" => "string",
 *          "zone5" => "string",
 *          "zone6" => "string",
 *      ]
 * ]
 * @property array    $settings
 * @property array    $schedule    = [
 *     "durationEnable" => "boolean",
 *     "startDate" => "string",
 *     "endDate" => "string",
 *     "timeEnable" => "boolean",
 *     "startTime" => "boolean",
 *     "endTime" => "boolean",
 *     "weekdays" => [1, 2, 3, 4, 5, 6, 7],
 *     "monthdays" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 26, 27, 28,
 *     29, 30, 31],
 * ]
 * @property string[] $groupIds
 * @property string[] $labels
 */
class Playlist extends PiSignageModel {
    protected static string $key = "name";

    protected static array $updatable = [
        "name",
        "version",
        "layout",
        "templateName",
        "videoWindow",
        "zoneVideoWindow",
        "assets",
        "settings",
        "schedule",
        "groupIds",
        "labels",
    ];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/playlists")->parser(new MultipleResourcesParser(static::class)),
            "create" => Endpoint::post("/playlists"),
            "get"    => Endpoint::get("/playlists/{name}")->parser(new SingleResourcesParser(static::class)),
            "update" => Endpoint::post("/playlists/{name}")->parser(new SingleResourcesParser(static::class)),
            "delete" => Endpoint::delete("/files/__{name}.json"),
        ];
    }

    /**
     * @param PiSignageClient                                                         $client
     * @param                                                                         $name
     * @return mixed
     */
    public static function make(PiSignageClient $client, $name): Playlist {
        $playlist       = new Playlist($client);
        $playlist->file = $name;
        $playlist->create();

        $playlist = static::get($client, $name);
        // Plot-twist, single response from the API do NOT include the playlist name
        $playlist->name = $name;

        return $playlist;
    }

    public static function get(PiSignageClient $client, $name): ?Playlist {
        $playlist = (new static($client))->callAction("get", ["name" => $name]);

        if (!$playlist) {
            return null;
        }

        // Playlist name of the playlist is not included in the response
        $playlist->name = $name;
        return $playlist;
    }
}
