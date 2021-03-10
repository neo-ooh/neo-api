<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Skin.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Database\Eloquent\Collection;
use Neo\BroadSign\Endpoint;

/**
 * Class Support
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    domain_id
 * @property int    geometry_id 1: percent of screen / 2: pixels
 * @property int    height
 * @property int    id
 * @property int    interactivity_timeout
 * @property int    interactivity_trigger_id
 * @property int    loop_policy_id
 * @property string name
 * @property int    parent_id
 * @property int    screen_no
 * @property int width
 * @property int x
 * @property int y
 * @property int z
 *
 * @method static Skin[] all()
 * @method static Skin[] get(int $frameID)
 * @method static Skin[] byReservable(array $params)
 * @method static Collection byDisplayUnit(array $params)
 */
class Skin extends BroadSignModel {

    protected static string $unwrapKey = "skin";

    protected static function actions(): array {
        return [
            "all" => Endpoint::get("/skin/v7")->multiple(),
            "get" => Endpoint::get("/skin/v7/{id}")->cache(3600),
            "byReservable" => Endpoint::get("/skin/v7/by_display_unit?display_unit_id={id}")->multiple(),
            "byDisplayUnit" => Endpoint::get("/skin/v7/by_display_unit")->multiple(),
        ];
    }
}
