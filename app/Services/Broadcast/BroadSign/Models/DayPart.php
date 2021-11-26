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
 * @property string start_date
 * @property string start_time
 * @property string virtual_end_date
 * @property string virtual_start_date
 * @property int    weight
 *
 * @method static DayPart get(BroadsignClient $client, int $dayPartId)
 */
class DayPart extends BroadSignModel {

    protected static string $unwrapKey = "day_part";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "get"        => Endpoint::get("/day_part/v5/{id}")
                                    ->unwrap(static::$unwrapKey)
                                    ->parser(new SingleResourcesParser(static::class))
                                    ->cache(3600),
            "bySchedule" => Endpoint::get("/day_part/v5/by_display_unit")
                                    ->unwrap(static::$unwrapKey)
                                    ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    public static function getByDisplayUnit(BroadsignClient $client, int $displayUnitId): Collection {
        return static::bySchedule($client, ["display_unit_id" => $displayUnitId]);
    }
}
