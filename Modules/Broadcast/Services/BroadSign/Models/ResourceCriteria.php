<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceCriteria.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Campaigns
 *
 * @package Neo\BroadSign\Models
 *
 * @property int  $id
 * @property int  $criteria_id
 * @property bool $active
 * @property int  $domain_id
 * @property int  $parent_id
 * @property int  $type
 *
 * @method static Collection forResource(BroadSignClient $client, array $params)
 *
 */
class ResourceCriteria extends BroadSignModel {

    protected static string $unwrapKey = "resource_criteria";

    protected static array $updatable = [
        "id",
        "active",
    ];

    /**
     * @return array<string, Endpoint>
     */
    protected static function actions(): array {
        return [
            "update"      => Endpoint::put("/resource_criteria/v7")
                                     ->unwrap(static::$unwrapKey)
                                     ->parser(new SingleResourcesParser(static::class)),
            "forResource" => Endpoint::get("/resource_criteria/v7")
                                     ->unwrap(static::$unwrapKey)
                                     ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /**
     * @param BroadSignClient $client
     * @param int             $resourceId
     * @return Collection<static>
     */
    public static function for(BroadSignClient $client, int $resourceId): Collection {
        return static::forResource($client, [
            "parent_id" => $resourceId,
        ]);
    }

    public function criteria(): Criteria {
        return Criteria::get($this->api, $this->criteria_id);
    }
}
