<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Schedule.php
 */

namespace Neo\BroadSign\Models;

use Neo\BroadSign\Endpoint;

/**
 * Class Schedules
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    day_of_week_mask
 * @property int    domain_id
 * @property string end_date
 * @property string end_time
 * @property int    id
 * @property string name
 * @property int    parent_id
 * @property int    reservable_id
 * @property int    rotation_mode
 * @property int    schedule_group
 * @property string start_date
 * @property string start_time
 * @property int weight
 *
 * @method static Schedule[] all()
 * @method static Schedule get(int $scheduleID)
 */
class Schedule extends BroadSignModel {

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
            "all" => Endpoint::get("/schedule/v8")->multiple(),
            "create" => Endpoint::post("/schedule/v8/add")->id(),
            "get" => Endpoint::get("/schedule/v8/{id}"),
            "update" => Endpoint::put("/schedule/v8")->id()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function campaign(): Campaign {
        return Campaign::get($this->reservable_id);
    }
}
