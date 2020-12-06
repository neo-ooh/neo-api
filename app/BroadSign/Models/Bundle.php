<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Bundle.php
 */

namespace Neo\BroadSign\Models;

use Facade\FlareClient\Http\Exceptions\BadResponse;
use Neo\BroadSign\Endpoint;

/**
 * A Bundle is Broadsign terminology for a Contents
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool active
 * @property bool allow_custom_duration
 * @property int  attributes
 * @property int  auto_synchronized
 * @property int  category_id
 * @property int  container_id
 * @property int  domain_id
 * @property int  fullscreen
 * @property int  id
 * @property int  interactivity_timeout
 * @property int  interactivity_trigger_id
 * @property int     loop_category_id
 * @property int     loop_positions
 * @property int     loop_weight
 * @property int     max_duration_msec
 * @property int     name
 * @property int     parent_id
 * @property int     position
 * @property int     secondary_sep_category_ids
 * @property int     trigger_category_id
 *
 * @static  int create(array $properties)
 */
class Bundle extends BroadSignModel {

    protected static string $unwrapKey = "bundle";

    protected static function actions (): array {
        return [
            "all"       => Endpoint::get("/bundle/v12")->multiple(),
            "create"    => Endpoint::post("/bundle/v12/add")->id(),
            "update"    => Endpoint::put("/bundle/v12"),
            "associate" => Endpoint::post("/bundle_content/v5/add")->ignore(),
        ];
    }

    /**
     * @param int $creativeID
     *
     * @return void
     * @throws BadResponse
     *
     */
    public function associateCreative (int $creativeID): void {
        $this->callAction("associate",
            [
                "content_id" => $creativeID,
                "parent_id"  => $this->id,
            ]);
    }
}
