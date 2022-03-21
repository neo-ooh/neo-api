<?php

/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTraffic.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $property_id
 * @property int     $year
 * @property int     $week
 * @property int     $traffic
 * @property boolean $is_estimate
 */
class PropertyTraffic extends Model {
    protected $table = "properties_traffic";

    protected $primaryKey = null;
    public $incrementing = false;

    protected $casts = [
        "is_estimate" => "boolean",
        "year"        => "integer",
    ];

    protected $fillable = [
        "property_id",
        "year",
        "week",
        "traffic",
        "is_estimate",
    ];

    public $timestamps = false;
}
