<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Inventory.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * Neo\Models\FormatLayout
 *
 * @property int   skin_id
 * @property int   year
 * @property array bookings
 * @property int   max_booking
 * @property int   name
 * @property Date  start_date
 * @property Date  end_date
 * @property Date  created_at
 * @property Date  updated_at
 *
 * @mixin Builder
 */
class Inventory extends Model {
    use HasCompositePrimaryKey;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    protected $primaryKey = ["skin_id", "year"];
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "skin_id",
        "year",
        "location_id",
        "bookings",
        "max_booking",
        "name",
        "start_date",
        "end_date"
    ];

    /**
     * The attributes that should be casted
     *
     * @var array
     */
    protected $casts = [
        "bookings" => "array"
    ];

    protected $dates = ["start_date", "end_date"];
}
