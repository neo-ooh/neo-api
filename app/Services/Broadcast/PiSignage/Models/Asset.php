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
 * @property string $name
 * @property string $size
 * @property string $ctime
 * @property string $path
 * @property string $type
 * @property string $dbdata
 *
 */
class Asset extends PiSignageModel {
    protected static array $updatable = [
        "name",
        "type",
        "duration",
        "size",
        "thumbnails",
        "validity",
        "playlists",
    ];

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/files")->parser(new MultipleResourcesParser(static::class)),
            "createStatic" => Endpoint::post("/files")
                                      ->multipart()
                                      ->parser(new SingleResourcesParser(static::class)),
            "get"          => Endpoint::get("/files/{id}")->parser(new SingleResourcesParser(static::class)),
            "update"       => Endpoint::post("/files/{id}")->parser(new SingleResourcesParser(static::class)),
            "delete"       => Endpoint::post("/files/{id}"),
        ];
    }

    public static function makeStatic(PiSignageClient $client, string $filename, $file_content) {
        return static::createStatic($client, [
            "multipart" => [
                [
                    "name"     => "assets",
                    "contents" => $file_content,
                    "filename" => $filename
                ]
            ]
        ]);
    }
}
