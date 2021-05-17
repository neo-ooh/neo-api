<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopPolicy.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;

/**
 * Class LoopPolicy
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property string $attributes
 * @property int    $container_id
 * @property int    $default_slot_duration
 * @property int    $domain_id
 * @property int    $filler_maximum_unique_content
 * @property int    $id
 * @property string $loop_share_configuration
 * @property string $loop_transform_strategy
 * @property int    $max_duration_msec
 * @property string $name
 * @property bool   $overbookable
 * @property int    $primary_inventory_share_msec
 * @property string $synchronization_set
 * @property int    $synchronization_type
 *
 * @property int    $max_booking
 *
 * @method static LoopPolicy[] all(BroadsignClient $client)
 * @method static LoopPolicy[] get(BroadsignClient $client, int $loopPolicyID)
 */
class LoopPolicy extends BroadSignModel {

    protected static string $unwrapKey = "loop_policy";

    protected static function actions(): array {
        return [
            "all" => Endpoint::get("/loop_policy/v10/all")
                             ->unwrap(static::$unwrapKey)
                             ->parser(new MultipleResourcesParser(static::class)),
            "get" => Endpoint::get("/loop_policy/v10/{id}")
                             ->unwrap(static::$unwrapKey)
                             ->parser(new SingleResourcesParser(static::class))
                             ->cache(360),
        ];
    }
}
