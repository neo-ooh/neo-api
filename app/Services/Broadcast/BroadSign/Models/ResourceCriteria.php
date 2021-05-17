<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceCriteria.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Services\Broadcast\BroadSign\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;

/**
 * Class Campaigns
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool active
 * @property int criteria_id
 * @property int domain_id
 * @property int id
 * @property int parent_id
 * @property int type
 *
 * @method static Collection forResource(BroadsignClient $client, array $params)
 *
 */
class ResourceCriteria extends BroadSignModel {

    protected static string $unwrapKey = "resource_criteria";

    protected static array $updatable = [
        "id",
        "active"
    ];

    protected static function actions(): array {
        return [
            "update" => Endpoint::put("/resource_criteria/v7")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
            "forResource" => Endpoint::get("/resource_criteria/v7")
                                     ->unwrap(static::$unwrapKey)
                                     ->parser(new MultipleResourcesParser(static::class)),
        ];
    }


    public static function for(BroadsignClient $client, int $resourceId): Collection {
        return static::forResource($client, [
            "parent_id" => $resourceId,
        ]);
    }
}
