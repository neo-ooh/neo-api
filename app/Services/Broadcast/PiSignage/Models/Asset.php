<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Asset.php
 */

namespace Neo\Services\Broadcast\PiSignage\Models;

use Illuminate\Support\Str;
use Neo\Models\Creative;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\PiSignage\API\PiSignageClient;

/**
 * Class Group
 *
 * @package Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string $name
 * @property string $size
 * @property string $ctime
 * @property string $path
 * @property string $type
 * @property ?array $dbdata
 *
 */
class Asset extends PiSignageModel {
    protected static array $updatable = [
        "_id",
        "name",
        "type",
        "duration",
        "size",
        "thumbnails",
        "dbdata",
        "validity",
        "playlists",
    ];

    protected static function actions(): array {
        return [
            "all"           => Endpoint::get("/files")->parser(new MultipleResourcesParser(static::class)),
            "createStatic"  => Endpoint::post("/files")
                                       ->multipart()
                                       ->parser(new MultipleResourcesParser(static::class)),
            "createDynamic" => Endpoint::post("/links")
                                       ->parser(new MultipleResourcesParser(static::class)),
            "get"           => Endpoint::get("/files/{name}")->parser(new SingleResourcesParser(static::class)),
            "update"        => Endpoint::post("/files/{name}")->parser(new SingleResourcesParser(static::class)),
            "delete"        => Endpoint::delete("/files/{name}"),
            "postupload"    => Endpoint::post("/postupload"),
        ];
    }

    public static function makeStatic(PiSignageClient $client, string $filename, $file_content) {
        static::createStatic($client, [
            [
                "name"     => "assets",
                "contents" => $file_content,
                "filename" => $filename,
            ]
        ], ["Accept" => "application/json"])[0];

        static::postupload($client, [
            "files" => [
                [
                    "name" => $filename,
                ]
            ]
        ]);
    }

    public static function makeDynamic(PiSignageClient $client, string $filename, $url) {
        static::createDynamic($client, [
            "details" => [
                "name" => Str::endsWith($filename, ".link") ? substr($filename, 0, -5) : $filename,
                "type" => ".link",
                "link" => $url,
            ]
        ]);
    }

    public static function get(...$args) {
        $asset = parent::get(...$args);

        if ($asset->dbdata) {
            $asset->_id = $asset->dbdata["_id"];
        }

        return $asset;
    }

    public static function inferNameFromCreative(Creative $creative, int $scheduleId): ?string {
        return match ($creative->type) {
            Creative::TYPE_STATIC  => $creative->id . "@" . $scheduleId . "." . $creative->properties->extension,
            Creative::TYPE_DYNAMIC => $creative->id . "@" . $scheduleId . ".link",
            default                => null,
        };

    }
}
