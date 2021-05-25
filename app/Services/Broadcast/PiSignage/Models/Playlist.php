<?php

namespace Neo\Services\Broadcast\PiSignage\Models;

use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\PiSignage\API\PiSignageClient;

/**
 * Class Group
 *
 * @package Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string   $name
 * @property integer $version
 * @property string $layout
 * @property string   $templateName
 * @property array $videoWindow = [
 *  "length" => "integer",
 *  "width" => "integer",
 *  "xoffset" => "integer",
 *  "yoffset" => "integer"
 * ]
 * @property array $zoneVideoWindow
 * @property array $asset = [
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
 * @property array $settings
 * @property array $schedule = [
 *     "durationEnable" => "boolean",
 *     "startDate" => "string",
 *     "endDate" => "string",
 *     "timeEnable" => "boolean",
 *     "startTime" => "boolean",
 *     "endTime" => "boolean",
 *     "weekdays" => [1, 2, 3, 4, 5, 6, 7],
 *     "monthdays" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 26, 27, 28, 29, 30, 31],
 * ]
 * @property string[] $groupIds
 * @property string[] $labels
 *
 *
 * @method static get(PiSignageClient $client, array $name)
 *
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
            "delete" => Endpoint::post("/playlists/{name}"),
        ];
    }

    /**
     * @param PiSignageClient $client
     * @param                 $name
     * @return mixed
     */
    public static function make(PiSignageClient $client, $name): Playlist {
        $playlist = new Playlist($client);
        $playlist->file = $name;
        $playlist->create();

        return static::get($client, ["name" => $name]);
    }
}
