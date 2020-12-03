<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\BroadSign\Models;

use Neo\BroadSign\Endpoint;

/**
 * Class LoopPolicy
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property string attributes
 * @property int    container_id
 * @property int    default_slot_duration
 * @property int    domain_id
 * @property int    filler_maximum_unique_content
 * @property int    id
 * @property string loop_share_configuration
 * @property string loop_transform_strategy
 * @property int    max_duration_msec
 * @property string name
 * @property bool   overbookable
 * @property int    primary_inventory_share_msec
 * @property string synchronization_set
 * @property int    synchronization_type
 *
 * @property int    max_booking
 *
 * @method static LoopPolicy[] all()
 * @method static LoopPolicy[] get(int $loopPolicyID)
 */
class LoopPolicy extends BroadSignModel {

    protected static string $unwrapKey = "loop_policy";

    protected static function actions(): array {
        return [
            "all" => Endpoint::get("/loop_policy/v10/all")->multiple(),
            "get" => Endpoint::get("/loop_policy/v10/{id}"),
        ];
    }
}
