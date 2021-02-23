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

use Neo\BroadSign\Endpoint;

/**
 * Class Campaigns
 *
 * @package Neo\BroadSign\Models
 *
 * @property int id
 * @property string resource_type
 *
 */
class ResourceQuery extends BroadSignModel {
    protected static string $unwrapKey = "resource_query";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "queryByName" => Endpoint::post("/resource_query/v3/query_by_name")->multiple(),
        ];
    }


    public static function byName(string $value, string $type) {
        return static::queryByName([
            "resource_type" => $type,
            "value" => $value,
        ]);
    }
}
