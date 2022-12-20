<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Schedule.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\Schedule as ScheduleResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Schedules
 *
 * @implements ResourceCastable<ScheduleResource>
 *
 * @property bool   $active
 * @property int    $day_of_week_mask
 * @property int    $domain_id
 * @property string $end_date
 * @property string $end_time
 * @property int    $id
 * @property string $name
 * @property int    $parent_id
 * @property int    $reservable_id
 * @property int    $rotation_mode
 * @property int    $schedule_group
 * @property string $start_date
 * @property string $start_time
 * @property int    $weight
 *
 * @method static Schedule[] all(BroadSignClient $client)
 * @method static Schedule|null get(BroadSignClient $client, int $scheduleID)
 */
class Schedule extends BroadSignModel implements ResourceCastable {

    protected static string $unwrapKey = "schedule";

    protected static array $updatable = [
        "active",
        "day_of_week_mask",
        "domain_id",
        "end_date",
        "end_time",
        "id",
        "name",
        "rotation_mode",
        "start_date",
        "start_time",
        "weight",
    ];

    protected static function actions(): array {
        return [
            "all"    => Endpoint::get("/schedule/v8")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new MultipleResourcesParser(static::class)),
            "create" => Endpoint::post("/schedule/v8/add")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new ResourceIDParser()),
            "get"    => Endpoint::get("/schedule/v8/{id}")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
            "update" => Endpoint::put("/schedule/v8")
                                ->unwrap(static::$unwrapKey)
                                ->parser(new SingleResourcesParser(static::class)),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function campaign(): Campaign {
        return Campaign::get($this->api, $this->reservable_id);
    }


    /**
     * @return ScheduleResource
     */
    public function toResource(): ScheduleResource {
        return new ScheduleResource(
            enabled       : $this->active,
            name          : $this->name,
            start_date    : $this->start_date,
            start_time    : $this->start_time,
            end_date      : $this->end_date,
            end_time      : $this->end_time,
            broadcast_days: $this->day_of_week_mask,
            order         : -1,
        );
    }
}
