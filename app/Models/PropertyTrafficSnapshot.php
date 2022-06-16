<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTrafficSnapshot.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $property_id
 * @property Carbon $date
 * @property int[]  $traffic
 */
class PropertyTrafficSnapshot extends Model {
    protected $table = "properties_traffic_snapshots";

    protected $primaryKey = null;

    protected $dates = [
        "date"
    ];

    public $timestamps = false;

    protected $casts = [
        "traffic" => "array"
    ];
}
