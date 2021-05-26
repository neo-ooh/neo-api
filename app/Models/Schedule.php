<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
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
use Neo\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;
use Neo\Services\Broadcast\Broadcast;

/**
 * Neo\Models\Branding
 *
 * - Model Attributes
 *
 * @property int                $id
 * @property int                $campaign_id
 * @property int                $content_id
 * @property int                $owner_id
 * @property int                $external_id_1
 * @property int                $external_id_2
 * @property Date               $start_date
 * @property Date               $end_date
 * @property int                $order
 * @property bool               $locked
 * @property bool               $is_approved
 * @property int                $print_count
 *
 * - Custom Attributes
 * @property string             $status
 *
 * - Relations
 * @property Campaign           $campaign
 * @property Content            $content
 * @property Actor              $owner
 * @property Collection<Review> $reviews
 *
 * - Relations Count
 * @property int                $reviews_count
 *
 * @mixin Builder
 */
class Schedule extends Model {
    use SoftDeletes;

    public const STATUS_DRAFT = "draft";
    public const STATUS_PENDING = "pending";
    public const STATUS_APPROVED = "approved";
    public const STATUS_LIVE = "broadcasting";
    public const STATUS_REJECTED = "rejected";
    public const STATUS_EXPIRED = "expired";
    public const STATUS_TRASHED = "trashed";

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
    protected $table = "schedules";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "campaign_id",
        "content_id",
        "owner_id",
        "start_date",
        "end_date",
        "order",
        "locked"
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
        'is_approved' => 'boolean',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["reviews"];

    /**
     * The relationships counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = ["reviews"];

    /**
     * The attributes that should always be loaded.
     *
     * @var array
     */
    protected $appends = [
        "status",
        "available_options"
    ];

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function boot() {
        parent::boot();

        static::deleting(function (Schedule $schedule) {
            // Execute the deletion on broadsign side
            if ($schedule->external_id_2 === null || $schedule->campaign->network_id === null) {
                return;
            }

            $network = Broadcast::network($schedule->campaign->network_id);

            $network->destroySchedule($schedule->id);
            $network->updateCampaignSchedulesOrder($schedule->campaign_id);


            // Adjust order of remaining schedules in the campaign
            foreach ($schedule->campaign->schedules as $s) {
                if($s->id === $schedule->id) {
                    continue;
                }

                if ($s->order >= $schedule->order) {
                    $s->decrement('order', 1);
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function campaign(): BelongsTo {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id')->withTrashed();
    }

    /**
     * @return mixed
     */
    public function content() {
        return $this->belongsTo(Content::class, 'content_id', 'id')->withTrashed();
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function getStatusAttribute(): string {
        if ($this->trashed()) {
            return self::STATUS_TRASHED;
        }

        if (!$this->locked) {
            return self::STATUS_DRAFT;
        }

        // Schedule is locked
        // Check reviews and its content pre-approval
        if (!$this->is_approved) {
            // Schedule's content is not pre-approved,
            // Is their a review for it ?
            if ($this->reviews_count === 0) {
                return self::STATUS_PENDING;
            }

            // Is the last review valid ?
            if (!$this->reviews->first()->approved) {
                return self::STATUS_REJECTED;
            }
        }

        // Has broadcasting finished ?
        if ($this->end_date < Date::now()) {
            return self::STATUS_EXPIRED; // Finish
        }

        // The schedule is approved, has broadcasting started ?
        if ($this->start_date > Date::now()) {
            return self::STATUS_APPROVED; // Not started
        }

        // This schedule is live
        return self::STATUS_LIVE;
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function reviews(): HasMany {
        return $this->hasMany(Review::class, 'schedule_id', 'id')->orderByDesc("created_at");
    }

    public function getAvailableOptionsAttribute(): array {
//        $network = $this->campaign->network;

        $options = ["dates", "time"];

//        switch ($network->broadcaster_connection->broadcaster) {
//            case Broadcaster::BROADSIGN:
//        }

        return $options;
    }
}
