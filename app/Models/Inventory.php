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
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCompositePrimarykey;

/**
 * Neo\Models\FormatLayout
 *
 * @property int   skin_id
 * @property int   year
 * @property array bookings
 * @property int   max_booking
 * @property Date  created_at
 * @property Date  updated_at
 *
 * @mixin Builder
 */
class Inventory extends Model {
    use HasCompositePrimarykey;
    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


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
        "bookings",
        "max_booking",
    ];

    /**
     * The attributes that should be casted
     *
     * @var array
     */
    protected $casts = [
        "bookings" => "array"
    ];
}
