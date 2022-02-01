<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DayPart.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class Criteria
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    day_mask
 * @property int    domain_id
 * @property string end_date
 * @property string end_time
 * @property int    id
 * @property int    impressions_per_hour
 * @property string minute_mask
 * @property string name
 * @property int    parent_id
 * @property string start_date\
 * @property string start_time
 * @property string virtual_end_date
 * @property string virtual_start_date
 * @property int    weight
 *
 * @method static Collection<DayPart> all(BroadsignClient $client)
 * @method static DayPart get(BroadsignClient $client, int $dayPartId)
 */
class DayPart extends BroadSignModel {

    protected static string $unwrapKey = "day_part";

    protected static array $updatable = [
        "active",
        "day_mask",
        "domain_id",
        "end_date",
        "end_time",
        "id",
        "impressions_per_hour",
        "minute_mask",
        "name",
        "start_date",
        "start_time",
        "virtual_end_date",
        "virtual_start_date",
        "weight",
    ];

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/day_part/v5/")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/day_part/v5/{id}")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new SingleResourcesParser(static::class))
                                      ->cache(3600),
            "get_multiple" => Endpoint::get("/day_part/v5/by_id")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "update"       => Endpoint::put("/day_part/v5")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new ResourceIDParser()),
            "bySchedule"   => Endpoint::get("/day_part/v5/by_display_unit")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /**
     * @param BroadsignClient $client
     * @param int             $displayUnitId
     * @return Collection<DayPart>
     */
    public static function getByDisplayUnit(BroadsignClient $client, int $displayUnitId): Collection {
        return static::bySchedule($client, ["display_unit_id" => $displayUnitId]);
    }

    /**
     * Pull multiple day parts at once using their ids
     *
     * @param BroadsignClient $client
     * @param array           $loopPoliciesIds
     * @return Collection<static>
     */
    public static function getMultiple(BroadsignClient $client, array $loopPoliciesIds) {
        return static::get_multiple($client, ["ids" => implode(",", $loopPoliciesIds)]);
    }
}
