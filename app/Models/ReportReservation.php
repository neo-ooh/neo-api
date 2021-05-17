<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReportReservation.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Neo\Models\Branding
 *
 * @property int    id
 * @property int    external_id
 * @property int    report_id
 * @property string name
 * @property string internal_name
 * @property Carbon start_date
 * @property Carbon end_date
 *
 * @mixin Builder
 */
class ReportReservation extends Model {
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
    protected $table = 'reports_reservations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'external_id',
        'report_id',
        'name',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "start_date" => "datetime",
        "end_date" => "datetime",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["reviewer"];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function schedule(): BelongsTo {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(Actor::class, 'reviewer_id', 'id');
    }
}
