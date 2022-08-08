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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Actor;
use Neo\Models\Traits\WithPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Jobs\Schedules\PromoteScheduleJob;
use Neo\Modules\Broadcast\Rules\AccessibleSchedule;
use Neo\Modules\Broadcast\Services\Resources\Schedule as ScheduleResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Neo\Models\Branding
 *
 * - OdooModel Attributes
 *
 * @property int                        $id
 * @property int                        $campaign_id
 * @property int                        $content_id
 * @property int                        $owner_id
 * @property Date                       $start_date
 * @property Date                       $start_time
 * @property Date                       $end_date
 * @property Date                       $end_time
 * @property int                        $broadcast_days
 * @property int                        $order
 * @property bool                       $is_locked
 *
 * @property ScheduleDetails            $details
 *
 * - Custom Attributes
 * @property float                      $length
 * @property ScheduleStatus             $status
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
    use WithPublicRelations;

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
     * @var array<string>
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
     * @var array<string, string>
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
     * @var array<string>
     */
    protected $with = ["details"];

    /**
     * The attributes that should always be loaded.
     *
     * @var array<string>
     */
    protected $appends = [
        "details",
        "status",
    ];

    protected string $accessRule = AccessibleSchedule::class;

    protected array $publicRelations = [
        "content" => "content",
        "reviews" => "reviews",
        "owner"   => "owner",
    ];

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function boot(): void {
        parent::boot();

        static::deleting(static function (Schedule $schedule) {
            // Clean up all reviews for the schedule
            $schedule->reviews()->delete();

            // Dispatch job to delete schedules in broadcasters
            DeleteScheduleJob::dispatch($schedule->getKey());
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasOne<ScheduleDetails>
     */
    public function details(): HasOne {
        return $this->hasOne(ScheduleDetails::class, 'schedule_id', 'id');
    }

    /**
     * @return BelongsTo<Actor, Schedule>
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    /**
     * @return BelongsTo<Campaign, Schedule>
     */
    public function campaign(): BelongsTo {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo<Content, Schedule>
     */
    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * @return HasMany<ScheduleReview>
     */
    public function reviews(): HasMany {
        return $this->hasMany(ScheduleReview::class, 'schedule_id', 'id')->orderByDesc("created_at");
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getStatusAttribute(): ScheduleStatus {
        if ($this->trashed()) {
            return ScheduleStatus::Trashed;
        }

        if (!$this->is_locked) {
            return ScheduleStatus::Draft;
        }

        // Schedule is locked
        // Check reviews and its content pre-approval
        if (!$this->details->is_approved) {
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

    public function getLengthAttribute(): float {
        return $this->content->duration;
    }


    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    */

    /**
     * @throws UnknownProperties
     */
    public function toResource(): ScheduleResource {
        return new ScheduleResource([
            "enabled"        => $this->status === ScheduleStatus::Approved || $this->status === ScheduleStatus::Live,
            "name"           => $this->campaign->name . " - " . $this->content->name,
            "start_date"     => $this->start_date->toDateString(),
            "start_time"     => $this->start_time->toTimeString(),
            "end_date"       => $this->end_date->toDateString(),
            "end_time"       => $this->end_date->toTimeString(),
            "broadcast_days" => $this->broadcast_days,
            "order"          => $this->order,
        ]);
    }

    /**
     * Get the external resources matching the given parameters
     *
     * @param int $broadcasterId
     * @param int $networkId
     * @param int $formatId
     * @return array<ExternalResource>
     */
    public function getExternalRepresentation(int $broadcasterId, int $networkId, int $formatId): array {
        return $this->external_representations->filter(function (ExternalResource $resource) use ($formatId, $networkId, $broadcasterId) {
            return $resource->broadcaster_id === $broadcasterId &&
                $resource->data->network_id === $networkId &&
                in_array($formatId, $resource->data->formats_id, true);
        })->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * Create update the schedule in broadcasters
     *
     * @return void
     */
    public function promote(): void {
        PromoteScheduleJob::dispatch($this->getKey());
    }
}
