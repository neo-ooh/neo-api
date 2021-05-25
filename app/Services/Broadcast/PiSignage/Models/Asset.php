<?php

namespace Neo\Services\Broadcast\PiSignage\Models;

use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\API\Parsers\SingleResourcesParser;

/**
 * Class Group
 *
 * @package Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string   $name
 * @property string   $size
 * @property string   $ctime
 * @property string   $path
 * @property string   $type
 * @property string   $dbdata
 *
 */
class Asset extends PiSignageModel {
    protected static array $updatable = [

    ];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/files")->parser(new MultipleResourcesParser(static::class)),
            "create" => Endpoint::post("/files")->parser(new SingleResourcesParser(static::class)),
            "get"    => Endpoint::get("/files/{id}")->parser(new SingleResourcesParser(static::class)),
            "update" => Endpoint::post("/files/{id}")->parser(new SingleResourcesParser(static::class)),
            "delete" => Endpoint::post("/files/{id}"),
        ];
    }
}
