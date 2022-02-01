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

use Illuminate\Support\Collection;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class LoopPolicy
 *
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
 * @method static Collection all(BroadsignClient $client)
 * @method static Collection get(BroadsignClient $client, int $loopPolicyID)
 */
class LoopPolicy extends BroadSignModel {

    protected static string $unwrapKey = "loop_policy";

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/loop_policy/v10")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/loop_policy/v10/{id}")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new SingleResourcesParser(static::class))
                                      ->cache(21600),
            "get_multiple" => Endpoint::get("/loop_policy/v10/by_id")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class))
                                      ->cache(21600),
        ];
    }

    /**
     * Pull multiple loop policies at once using their ids
     *
     * @param BroadsignClient $client
     * @param array           $loopPoliciesIds
     * @return Collection<static>
     */
    public static function getMultiple(BroadsignClient $client, array $loopPoliciesIds) {
        return static::get_multiple($client, ["ids" => implode(",", $loopPoliciesIds)]);
    }
}
