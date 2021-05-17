<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceQuery.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;

/**
 * Class Campaigns
 *
 * @package Neo\BroadSign\Models
 *
 * @property int    $id
 * @property string $resource_type
 *
 * @method static queryByName(BroadsignClient $client, string[] $array)
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


    public static function byName(BroadsignClient $client, string $value, string $type) {
        return static::queryByName($client, [
            "resource_type" => $type,
            "value"         => $value,
        ]);
    }
}
