<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceQuery.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class ResourceQuery
 *
 * @property int    $id
 * @property string $resource_type
 *
 * @method static Collection<static> queryByName(BroadSignClient $client, string[] $array)
 */
class ResourceQuery extends BroadSignModel {
    protected static string $unwrapKey = "resource_query";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "queryByName" => Endpoint::post("/resource_query/v3/query_by_name")
                                     ->unwrap(static::$unwrapKey)
                                     ->parser(new MultipleResourcesParser(static::class))
                                     ->domain(false),
        ];
    }


    public static function byName(BroadSignClient $client, string $value, string $type): Collection {
        return static::queryByName($client, [
            "resource_type" => $type,
            "value"         => $value,
        ]);
    }
}
