<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ResourceCriteria.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;

/**
 * Class Campaigns
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool active
 * @property int criteria_id
 * @property int domain_id
 * @property int id
 * @property int parent_id
 * @property int type
 *
 * @method static Collection forResource(array $params)
 *
 */
class ResourceCriteria extends BroadSignModel {

    protected static string $unwrapKey = "resource_criteria";

    protected static array $updatable = [
        "id",
        "active"
    ];

    protected static function actions(): array {
        return [
            "update" => Endpoint::put("/resource_criteria/v7")->id(),
            "forResource" => Endpoint::get("/resource_criteria/v7")->multiple(),
        ];
    }


    public static function for(int $resourceId) {
        return static::forResource([
            "parent_id" => $resourceId,
        ]);
    }
}
