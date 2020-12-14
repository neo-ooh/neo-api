<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Schedule.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\BroadSign\Jobs\DisableBroadSignSchedule;

/**
 * NeoModels\Branding
 *
 * - Model Attributes
 *
 * @property int                id
 * @property int                campaign_id
 * @property int                content_id
 * @property int                owner_id
 * @property int                broadsign_schedule_id
 * @property Date               start_date
 * @property Date               end_date
 * @property int                order
 * @property bool               locked
 * @property int                print_count
 *
 * - Custom Attributes
 * @property string             status
 * @property bool               is_approved
 *
 * - Relations
 * @property Campaign           campaign
 * @property Content            content
 * @property Actor              owner
 * @property Collection<Review> reviews
 *
 * - Relations Count
 * @property int                reviews_count
 *
 * @mixin Builder
 */
class Schedule extends Model {
    use SoftDeletes;

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
    protected $table = 'schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'content_id',
        'owner_id',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "broadsign_bundle_id",
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "start_date",
        "end_date",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'print_count' => 'integer',
        'locked'      => 'boolean',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [ "reviews" ];

    /**
     * The relationships counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = [ "reviews" ];

    /**
     * The attributes that should always be loaded.
     *
     * @var array
     */
    protected $appends = [
        "status",
    ];

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function boot () {
        parent::boot();

        static::deleted(function (Schedule $schedule) {
            DisableBroadSignSchedule::dispatch($schedule->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function campaign (): BelongsTo {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * @return mixed
     */
    public function content () {
        return $this->belongsTo(Content::class, 'content_id', 'id')->withTrashed();
    }

    public function owner (): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function getStatusAttribute (): string {
        $isTrashed = $this->trashed();

        if (!$isTrashed) {
            if (!$this->locked) {
                return 'draft';
            }

            // schedule is locked
            // is their a review for it ?
            if ($this->reviews_count === 0) {
                return 'pending';
            }

            // Is the last review valid ?
            if (!$this->reviews()->first()->approved) {
                return 'rejected';
            }

            // The schedule is approved, has broadcasting started ?
            if ($this->start_date > Date::now()) {
                return 'approved'; // Not started
            }
        }

        // Has broadcasting finished ?
        if ($this->end_date < Date::now()) {
            return 'expired'; // Finish
        }

        if ($isTrashed) {
            return 'trashed';
        }

        // This schedule is live
        return 'broadcasting';
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function reviews (): HasMany {
        return $this->hasMany(Review::class, 'schedule_id', 'id')->orderByDesc("created_at");
    }

    public function getIsApprovedAttribute (): bool {
        $status = $this->status;

        return $status === 'approved' || $status === 'broadcasting';
    }
}
