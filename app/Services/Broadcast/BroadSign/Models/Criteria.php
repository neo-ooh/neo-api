<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Criteria.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;

/**
 * Class Criteria
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool active
 * @property int  criteria_id
 * @property int  domain_id
 * @property int  id
 * @property int  parent_id
 * @property int  type
 *
 *
 * @method static Criteria[] all(BroadsignClient $client)
 * @method static int  create(BroadsignClient $client, array $attributes)
 */
class Criteria extends BroadSignModel {

    protected static string $unwrapKey = "resource_criteria";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/resource_criteria/v7")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new MultipleResourcesParser(static::class)),
            "create" => Endpoint::post("/resource_criteria/v7/add")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
        ];
    }
}
