<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleReview.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Actor;

/**
 * Neo\Models\Branding
 *
 * @property int      id
 * @property int      schedule_id
 * @property int      reviewer_id
 * @property bool     approved
 * @property string   message
 *
 * @property Schedule schedule
 * @property Actor    reviewer
 *
 * @mixin Builder
 */
class ScheduleReview extends Model {
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
    protected $table = 'schedule_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_id',
        'reviewer_id',
        'approved',
        'message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["reviewer:id,name"];


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
