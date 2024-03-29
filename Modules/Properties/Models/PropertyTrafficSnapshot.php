<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTrafficSnapshot.php
 */

namespace Neo\Modules\Properties\Models;

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


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "date"    => "datetime",
        "traffic" => "array",
    ];

    public $timestamps = false;
}
