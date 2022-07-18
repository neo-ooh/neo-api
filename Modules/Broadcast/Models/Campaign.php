<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Campaign.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon as Date;
use Illuminate\Support\Collection;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CampaignStatus;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Rules\AccessibleCampaign;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;

/**
 * Neo\Models\Campaigns
 *
 * @property int                  $id
 * @property int                  $owner_id
 * @property int                  $creator_id
 * @property string               $name
 * @property double               $schedules_default_length Content without a defined duration/length will use this length when
 *           being scheduled
 * @property double               $schedules_max_length     Maximum content length/duration allowed
 * @property int                  $occurrences_in_loop      Tell how many time in one loop the campaign should play, default to 1
 * @property int                  $priority                 Higher number means lower priority
 * @property Date                 $start_date               Y-m-d
 * @property Date                 $start_time               H:m:s
 * @property Date                 $end_date                 Y-m-d
 * @property Date                 $end_time                 H:m:s
 * @property Date                 $broadcast_days           Bit mask of the days of the week the campaign should run: 127 =>
 *           01111111 - all week
 *
 * @property Date                 $created_at
 * @property Date                 $updated_at
 * @property Date|null            $deleted_at
 *
 * @property CampaignStatus       $status
 *
 * @property Actor                $parent
 * @property Actor                $creator
 * @property Collection<Actor>    $shares
 *
 * @property int                  $schedules_count
 * @property Collection<Schedule> $schedules
 *
 * @property int                  $locations_count
 * @property Collection<Location> $locations
 *
 *
 * @property Collection<Location> $related_campaigns
 * @property array                $available_options
 *
 * @property Collection<integer>  $targeted_frames          Frame targeting criteria required by the campaign
 *           schedule
 *
 * @mixin Builder<Campaign>
 */
class Campaign extends BroadcastResourceModel {
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Model properties
    |--------------------------------------------------------------------------
    */

    public BroadcastResourceType $resourceType = BroadcastResourceType::Campaign;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "campaigns";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "owner_id",
        "creator_id",
        "name",
        "schedules_default_length",
        "schedules_max_length",
        "priority",
        "start_date",
        "start_time",
        "end_date",
        "end_time",
        "broadcast_days",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "display_duration" => "integer",
        "start_date"       => "date:Y-m-d",
        "end_date"         => "date:Y-m-d",
        "start_time"       => "date:H:i:s",
        "end_time"         => "date:H:i:s",
    ];

    protected $appends = [
        "status",
        "available_options"
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleCampaign::class;

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Campaign $campaign) {
            // Disable the campaign in BroadSign
            if ($campaign->external_id !== null) {
                Broadcast::network($campaign->network_id)->destroyCampaign($campaign->id);
            }

            // Delete all schedules in the campaign
            /** @var Schedule $schedule */
            foreach ($campaign->schedules as $schedule) {
                if ($campaign->isForceDeleting()) {
                    $schedule->forceDelete();
                } else {
                    $schedule->delete();
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Actors */

    /**
     * @return BelongsTo<Actor>
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(Actor::class, 'parent_id', 'id');
    }

    /**
     * @return BelongsTo<Actor>
     */
    public function creator(): BelongsTo {
        return $this->belongsTo(Actor::class, 'creator_id', 'id');
    }

    /**
     * @return BelongsToMany<Actor>
     */

    public function shares(): BelongsToMany {
        return $this->belongsToMany(Actor::class, "campaign_shares", "campaign_id", "actor_id");
    }

    /* Broadcast */

    /**
     * @return HasMany<Schedule>
     */
    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, "campaign_id", "id")->orderBy("order");
    }

    /**
     * @return BelongsToMany<Format>
     */
    public function formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "campaign_formats", "campaign_id", "format_id");
    }

    /**
     * @return BelongsToMany<Location>
     */
    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, "campaign_locations", "campaign_id", "location_id");
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * List the libraries ID determined to be relevant for the campaign
     *
     * @return Collection
     */
    public function getRelatedLibrariesAttribute(): Collection {
        // I. Select campaigns owned by the same user
        // II. Filter out the current one
        return $this->parent->getLibraries(true, false, false)->pluck('id');
    }

    public function getStatusAttribute(): CampaignStatus {
        // Is the campaign expired ?
        if ($this->end_date->isBefore(Date::now())) {
            return CampaignStatus::Empty;
        }

        if ($this->schedules->count() === 0) {
            return CampaignStatus::Offline;
        }

        // Does it has a pending schedule in it?
        if ($this->schedules->some("status", ScheduleStatus::Pending)) {
            return CampaignStatus::Pending;
        }

        // Does it has a valid schedule in it ?
        if ($this->schedules->some("status", ScheduleStatus::Live) || $this->schedules->some("status", ScheduleStatus::Approved)) {
            return CampaignStatus::Live;
        }

        return CampaignStatus::Offline;

    }

    public function getAvailableOptionsAttribute(): array {
        $options = [];

        $service = $this->network ? $this->network->broadcaster_connection->broadcaster : Broadcaster::BROADSIGN;

        switch ($service) {
            case Broadcaster::BROADSIGN:
                $options[] = "loop_saturation";
                break;
            case Broadcaster::PISIGNAGE:
                $options[] = "screens_controls";
                break;
        }

        return $options;
    }

}
