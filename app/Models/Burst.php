<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Burst.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon as Date;

/**
 * Class Burst
 *
 * @package Neo\Models
 *
 * @property int      id
 * @property int      report_id
 * @property int      location_id
 * @property int      requested_by
 * @property Date     start_at
 * @property bool     started
 * @property bool     is_finished
 * @property int      scale_factor
 * @property int      duration_ms
 * @property int      frequency_ms
 * @property Date     created_at
 * @property Date     updated_at
 *
 * @property Report   report
 * @property Location location
 * @property Collection screenshots
 * @property int screenshots_count
 *
 * @property int expected_screenshots
 *
 * @mixin Builder
 */
class Burst extends Model {
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
    protected $table = 'bursts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'player_id',
        'requested_by',
        'started_at',
        'status',
        'is_manual',
        'scale_factor',
        'duration_ms',
        'frequency_ms',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'started'    => 'boolean',
        'is_finished' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "started_at",
    ];

    /**
     * The attributes that should always be loaded
     *
     * @var array
     */
    protected $appends = [
        "expected_screenshots",
    ];

    /**
     * The relations count that should always be loaded
     *
     * @var array
     */
    protected $withCount = [
        "screenshots"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function report(): BelongsTo {
        return $this->belongsTo(Report::class, "report_id");
    }

    /**
     * @return BelongsTo
     */
    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, "location_id");
    }

    /**
     * @return HasMany
     */
    public function screenshots(): HasMany {
        return $this->hasMany(Screenshot::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, "requested_by");
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanism
    |--------------------------------------------------------------------------
    */


    public function getExpectedScreenshotsAttribute() {
        return ceil($this->duration_ms / $this->frequency_ms) + 1;
    }
}
