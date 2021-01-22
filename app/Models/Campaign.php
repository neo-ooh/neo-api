<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Campaign.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon as Date;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Factories\CampaignFactory;
use Neo\Rules\AccessibleCampaign;

/**
 * Neo\Models\Campaigns
 *
 * @property int                  id
 * @property string               broadsign_reservation_id
 * @property int                  owner_id
 * @property int                  format_id
 * @property string               name
 * @property int                  display_duration
 * @property int                  content_limit
 * @property int                  loop_saturation
 * @property Date                 start_date
 * @property Date                 end_date
 *
 * @property Actor                owner
 * @property Format               format
 * @property Collection<Schedule> schedules
 * @property Collection<Location> locations
 * @property Collection<Actor>    shares
 *
 * @property int                  locations_count
 * @property int                  schedules_count
 *
 * @property Collection<Campaign> related_campaigns
 *
 * @mixin Builder
 */
class Campaign extends SecuredModel {
    use HasFactory, SoftDeletes;

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
        'content_limit',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "reservation_id",
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
        'content_limit'    => 'integer',
        'display_duration' => 'integer',
    ];

    /**
     * The relationship counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = [
    ];

    protected $appends = [
        "status",
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleCampaign::class;

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

    public function getStatusAttribute(): string {
        $schedules = $this->schedules;
        foreach ($schedules as $schedule) {
            if ($schedule->status === "pending") {
                return "pending";
            }
        }

        return "ready";
    }

    /**
     * @return Collection
     *
     * @psalm-return Collection<Campaign>|array<empty, empty>
     */
    public function getRelatedCampaignsAttribute() {
        // I. Select campaigns owned by the same user
        // II. Filter out the current one
        return $this->owner
            ->getCampaigns(true, $this->owner_id === Auth::user()->details->parent_id, false, false)
            ->each(fn(/** Campaign */ $campaign) => $campaign->unsetRelations())
            ->each(fn(/** Campaign */ $campaign) => $campaign->load('format'))
            ->values();
    }

    public function getRelatedLibrariesAttribute(): \Illuminate\Support\Collection {
        // I. Select campaigns owned by the same user
        // II. Filter out the current one
        return $this->owner->getLibraries(true, false, false, true)->pluck('id');
    }
}
