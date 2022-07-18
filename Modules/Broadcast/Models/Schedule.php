<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Schedule.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Rules\AccessibleSchedule;
use Neo\Services\Broadcast\Broadcast;

/**
 * Neo\Models\Branding
 *
 * - Model Attributes
 *
 * @property int                        $id
 * @property int                        $campaign_id
 * @property int                        $content_id
 * @property int                        $owner_id
 * @property Date                       $start_date
 * @property Date                       $start_time
 * @property Date                       $end_date
 * @property Date                       $end_time
 * @property int                        $order
 * @property bool                       $locked
 * @property bool                       $is_approved
 * @property int                        $print_count
 *
 * - Custom Attributes
 * @property float                      $length
 * @property string                     $status
 *
 * - Relations
 * @property Actor                      $owner
 * @property Content                    $content
 * @property Campaign                   $campaign
 *
 * @property int                        $reviews_count
 * @property Collection<ScheduleReview> $reviews
 *
 * @mixin Builder
 */
class Schedule extends BroadcastResourceModel {
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Model properties
    |--------------------------------------------------------------------------
    */

    public BroadcastResourceType $resourceType = BroadcastResourceType::Schedule;
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
        "start_time",
        "end_date",
        "end_time",
        "order",
        "is_locked"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "is_locked" => "boolean",

        "start_date" => "date:Y-m-d",
        "start_time" => "date:H:m:s",
        "end_date"   => "date:Y-m-d",
        "end_time"   => "date:H:m:s",
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

    protected string $accessRule = AccessibleSchedule::class;

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
                if ($s->id === $schedule->id) {
                    continue;
                }

                if ($s->order >= $schedule->order && $s->order !== 0) {
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

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function campaign(): BelongsTo {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id')->withTrashed();
    }

    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id')->withTrashed();
    }

    public function reviews(): HasMany {
        return $this->hasMany(ScheduleReview::class, 'schedule_id', 'id')->orderByDesc("created_at");
    }

    public function getStatusAttribute(): ScheduleStatus {
        if ($this->trashed()) {
            return ScheduleStatus::Trashed;
        }

        if (!$this->locked) {
            return ScheduleStatus::Draft;
        }

        // Schedule is locked
        // Check reviews and its content pre-approval
        if (!$this->is_approved) {
            // Schedule's content is not pre-approved,
            // Is their a review for it ?
            if ($this->reviews_count === 0) {
                return ScheduleStatus::Pending;
            }

            // Is the last review valid ?
            if (!$this->reviews->first()->approved) {
                return ScheduleStatus::Rejected;
            }
        }

        // Has broadcasting finished ?
        if ($this->end_date < Date::now()) {
            return ScheduleStatus::Expired; // Finish
        }

        // The schedule is approved, has broadcasting started ?
        if ($this->start_date > Date::now()) {
            return ScheduleStatus::Approved; // Not started
        }

        // This schedule is live
        return ScheduleStatus::Live;
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getAvailableOptionsAttribute(): array {
//        $network = $this->campaign->network;

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        /** @noinspection OneTimeUseVariablesInspection */
        $options = ["dates", "time"];

//        switch ($network->broadcaster_connection->broadcaster) {
//            case Broadcaster::BROADSIGN:
//        }

        return $options;
    }

    public function getLengthAttribute(): float {
        return round($this->content->duration) > 0 ? $this->content->duration : $this->campaign->schedules_default_length;
    }
}
