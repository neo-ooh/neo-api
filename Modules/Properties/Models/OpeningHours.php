<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OpeningHours.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Neo\Models\Traits\HasCompositePrimaryKey;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;

/**
 * @property int    $property_id
 * @property int    $weekday 1-indexed day of the week.
 * @property Carbon $open_at
 * @property Carbon $close_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OpeningHours extends Model {
    use HasCompositePrimaryKey;

    protected $table = "opening_hours";

    protected $primaryKey = [
        "property_id",
        "weekday",
    ];

    public $incrementing = false;

    protected $casts = [
        "open_at"  => "datetime:H:i",
        "close_at" => "datetime:H:i",
    ];


    public function toInventoryResource() {
        return new DayOperatingHours(
            day     : $this->weekday,
            start_at: $this->open_at->toTimeString(),
            end_at  : $this->close_at->toTimeString()
        );
    }
}
