<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Criteria.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Criteria
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property string $attributes
 * @property int    $container_id
 * @property int    $domain_id
 * @property string $geo_data
 * @property int    $id
 * @property string $name
 * @property int    $type
 *
 *
 * @method static static[] all(BroadSignClient $client)
 * @method static static get(BroadSignClient $client, int $criteria_id)
 * @method static int  create(BroadSignClient $client, array $attributes)
 */
class Criteria extends BroadSignModel {
    protected static string $unwrapKey = "resource_criteria";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/resource_criteria/v8")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new MultipleResourcesParser(static::class)),
            "get"    => Endpoint::get("/resource_criteria/v8/{id}")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
            "create" => Endpoint::post("/resource_criteria/v8/add")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
        ];
    }
}
