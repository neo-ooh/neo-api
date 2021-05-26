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
            "all"          => Endpoint::get("/files")->parser(new MultipleResourcesParser(static::class)),
            "createStatic" => Endpoint::post("/files")
                                      ->multipart()
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/files/{name}")->parser(new SingleResourcesParser(static::class)),
            "update"       => Endpoint::post("/files/{name}")->parser(new SingleResourcesParser(static::class)),
            "delete"       => Endpoint::post("/files/{name}"),
            "postupload"       => Endpoint::post("/postupload"),
        ];
    }

    public static function makeStatic(PiSignageClient $client, string $filename, $file_content) {
        $asset = static::createStatic($client, [
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

        return;
    }

    public static function get(...$args) {
        $asset = parent::get(...$args);

        if($asset->dbdata) {
            $asset->_id = $asset->dbdata["_id"];
        }

        return $asset;
    }
}
