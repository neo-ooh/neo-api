<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopSlot.php
 */

namespace Neo\BroadSign\Models;

use Carbon\Traits\Date;
use Neo\BroadSign\Endpoint;

/**
 * Class LoopPolicy
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    day_of_week_mask
 * @property int    domain_id
 * @property int    duration
 * @property Date   end_date
 * @property string event_occurrence
 * @property int    id
 * @property int    inventory_category_id
 * @property int    parent_id
 * @property int    priority
 * @property int    reps_per_hour
 * @property Date    start_date
 *
 *
 * @method static LoopSlot[] forCampaign(array $parameters)
 */
class LoopSlot extends BroadSignModel {

    protected static string $unwrapKey = "loop_slot";

    protected static function actions (): array {
        return [
            "forCampaign" => Endpoint::get("/loop_slot/v10/by_reservable")->multiple(),
        ];
    }
}
