<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Campaign.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon as Date;
use Illuminate\Support\Collection;
use Neo\Models\Factories\CampaignFactory;
use Neo\Rules\AccessibleCampaign;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;

/**
 * Neo\Models\Campaigns
 *
 * @property int                 $id
 * @property int                 $network_id
 * @property string              $external_id
 * @property int                 $owner_id
 * @property int                 $format_id
 * @property string              $name
 * @property int                 $display_duration
 * @property int                 $loop_saturation
 * @property Date                $start_date
 * @property Date                $end_date
 *
 * @property Network             $network
 * @property Actor               $owner
 * @property Format              $format
 * @property EloquentCollection  $schedules
 * @property EloquentCollection  $locations
 * @property EloquentCollection  $shares
 *
 * @property int                 $locations_count
 * @property int                 $schedules_count
 *
 * @property EloquentCollection  $related_campaigns
 *
 * @property Collection<integer> $targeted_frames Frame targeting criteria required by the campaign
 *           schedule
 *
 * @mixin Builder
 */
class Campaign extends SecuredModel {
    use HasFactory, SoftDeletes;

    public const STATUS_NOT_STARTED = "not_started";
    public const STATUS_EMPTY = "empty";
    public const STATUS_PENDING = "pending";
    public const STATUS_LIVE = "live";
    public const STATUS_OFFLINE = "offline";
    public const STATUS_EXPIRED = "expired";

    /*
    |--------------------------------------------------------------------------
    | Model properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'forant_id',
        'name',
        'display_duration',
        'start_date',
        'end_date',
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
        'display_duration' => 'integer',
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

        static::deleting(function (Campaign $campaign) {
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

    protected static function newFactory(): CampaignFactory {
        return CampaignFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Direct */

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'campaign_id', 'id')->orderBy('order');
    }

    public function trashedSchedules(): HasMany {
        return $this->hasMany(Schedule::class, 'campaign_id', 'id')->onlyTrashed()->orderBy('order');
    }

    public function shares(): BelongsToMany {
        return $this->belongsToMany(Actor::class, 'campaign_shares', 'campaign_id', "actor_id");
    }

    /* Network */

    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }

    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, 'campaign_locations', 'campaign_id', "location_id");
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
        return $this->owner->getLibraries(true, false, false)->pluck('id');
    }

    public function getTargetedFramesAttribute(): Collection {

        // List the required criteria
        return $this
            ->format
            ->layouts
            ->pluck("frames")
            ->flatten()
            ->unique("id")
            ->values();
    }

    public function getStatusAttribute() {
        // Is the campaign expired ?
        if ($this->end_date->isBefore(Date::now())) {
            return static::STATUS_EXPIRED;
        }

        if ($this->schedules->count() === 0) {
            return static::STATUS_OFFLINE;
        }

        // Does it has a pending schedule in it?
        if ($this->schedules->some("status", Schedule::STATUS_PENDING)) {
            return static::STATUS_PENDING;
        }

        // Does it has a valid schedule in it ?
        if ($this->schedules->some("status", Schedule::STATUS_LIVE) || $this->schedules->some("status", Schedule::STATUS_APPROVED)) {
            return static::STATUS_LIVE;
        }

        return static::STATUS_OFFLINE;

    }

    public function getAvailableOptionsAttribute(): array {
        $options = [];

        $service = $this->network ? $this->network->broadcaster_connection->broadcaster : Broadcaster::BROADSIGN;

        switch ($service) {
            case Broadcaster::BROADSIGN:
                $options[] = "loop_saturation";
                break;
            case Broadcaster::PISIGNAGE:
                break;
        }

        return $options;
    }

}
