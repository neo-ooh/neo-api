<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourcePerformance.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Neo\Modules\Broadcast\Models\StructuredColumns\ResourcePerformanceData;

/**
 * @property int                     $resource_id
 * @property Carbon                  $recorded_at
 * @property int                     $repetitions
 * @property int                     $impressions
 * @property ResourcePerformanceData $data
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 *
 * @mixin Builder<ResourcePerformance>
 */
class ResourcePerformance extends Model {
    protected $table = "resource_performances";

    protected $casts = [
        "recorded_at" => "date:Y-m-d",
        "data"        => ResourcePerformanceData::class,
    ];

    protected $fillable = [
        "resource_id",
        "recorded_at",
        "repetitions",
        "impressions",
        "data",
    ];
}
