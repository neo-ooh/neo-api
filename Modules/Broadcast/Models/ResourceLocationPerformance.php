<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceLocationPerformance.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Neo\Modules\Broadcast\Models\StructuredColumns\ResourcePerformanceData;

/**
 * @property int                     $resource_id
 * @property int|null                $location_id
 * @property ResourcePerformanceData $data
 * @property int                     $repetitions
 * @property int                     $impressions
 *
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 */
class ResourceLocationPerformance extends Model {
    protected $table = "resource_location_performances";

    public $incrementing = false;

    protected $casts = [
        "data" => ResourcePerformanceData::class,
    ];

    protected $fillable = [
        "resource_id",
        "location_id",
        "data",
        "repetitions",
        "impressions",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function resource() {
        $this->belongsTo(BroadcastResource::class, "resource_id", "id");
    }

    public function location() {
        $this->belongsTo(Location::class, "location_id", "id");
    }
}
