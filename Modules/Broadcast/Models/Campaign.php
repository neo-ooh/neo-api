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
use Illuminate\Support\Carbon;
use Illuminate\Support\Carbon as Date;
use Illuminate\Support\Collection;
use Neo\Models\Actor;
use Neo\Models\Traits\WithPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CampaignStatus;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Jobs\Campaigns\DeleteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Campaigns\PromoteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Schedules\PromoteScheduleJob;
use Neo\Modules\Broadcast\Rules\AccessibleCampaign;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\Campaign as CampaignResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Neo\Models\Campaigns
 *
 * @property int                  $id
 * @property int                  $parent_id
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
 * @property int                  $broadcast_days           Bit mask of the days of the week the campaign should run: 127 =>
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
 * @mixin Builder<Campaign>
 */
class Campaign extends BroadcastResourceModel {
    use SoftDeletes;
    use WithPublicRelations;
    use HasRelationships;

    /*
    |--------------------------------------------------------------------------
    | OdooModel properties
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
        "parent_id",
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
        "start_time"       => "date:H:i:s",
        "end_date"         => "date:Y-m-d",
        "end_time"         => "date:H:i:s",
    ];

    protected $appends = [];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleCampaign::class;

    protected array $publicRelations = [
        "external_representations" => "external_representations",
        "parent"                   => "parent",
        "creator"                  => "creator",
        "shares"                   => "shares",
        "schedules"                => ["schedules.owner:id,name", "schedules.content.creatives"],
        "expired_schedules"        => ["expired_schedules.content"],
        "locations"                => "locations",
        "formats"                  => ["formats.layouts.frames"],
        "tags"                     => "broadcast_tags",
    ];

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Campaign $campaign) {
            // Dispatch job to delete campaign in broadcasters
            DeleteCampaignJob::dispatch($campaign->getKey());

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

    /**
     * @return BelongsTo<Actor, Campaign>
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(Actor::class, 'parent_id', 'id');
    }

    /**
     * @return BelongsTo<Actor, Campaign>
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

    /**
     * @return HasMany<Schedule>
     */
    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, "campaign_id", "id")
                    ->where("end_date", "<", Carbon::now())
                    ->orderBy("order");
    }

    /**
     * @return HasMany<Schedule>
     */
    public function expired_schedules(): HasMany {
        return $this->hasMany(Schedule::class, "campaign_id", "id")
                    ->withTrashed()
                    ->where("end_date", ">", Carbon::now())
                    ->orderBy("end_date");
    }

    /**
     * @return BelongsToMany<Location>
     */
    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, "campaign_locations", "campaign_id", "location_id")
                    ->withPivot(["format_id"])
                    ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Format>
     */
    public function formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "campaign_locations", "campaign_id", "format_id")
                    ->distinct();
    }

    /**
     * @return HasManyDeep<Layout>
     */
    public function layouts(): HasManyDeep {
        /**
         * @var HasManyDeep<Layout>
         */
        return $this->hasManyDeepFromRelations([$this->formats(), (new Format())->layouts()])
                    ->distinct();
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * Dispatch the appropriate job to replicate the campaign on broadcaster and keep it up to date
     *
     * @return void
     */
    public function promote(): void {
        PromoteCampaignJob::dispatch($this->getKey())->chain(
            $this->schedules->map(fn(Schedule $schedule) => new PromoteScheduleJob($schedule->getKey()))
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    */

    /**
     * @throws UnknownProperties
     */
    public function toResource(): CampaignResource {
        $this->loadMissing("parent");

        return new CampaignResource([
            "enabled"                      => $this->status === CampaignStatus::Live,
            "name"                         => $this->parent->name . " - " . $this->name,
            "start_date"                   => $this->start_date->toDateString(),
            "start_time"                   => $this->start_time->toTimeString(),
            "end_date"                     => $this->end_date->toDateString(),
            "end_time"                     => $this->end_date->toTimeString(),
            "broadcast_days"               => $this->broadcast_days,
            "priority"                     => $this->priority,
            "occurrences_in_loop"          => $this->occurrences_in_loop,
            "default_schedule_length_msec" => $this->schedules_default_length * 1000,
        ]);
    }

    /**
     * List all the different representations necessary for this campaign to run
     *
     * @return array<ExternalCampaignDefinition>
     */
    public function getExternalBreakdown(): array {
        // A campaign in Connect may be represented by multiple external campaign, across several broadcasters.
        // A campaign is broken down into multiple campaigns with the following criteria:
        //  1. Broadcaster/Network
        //  2. DisplayType

        // This is done using the list of locations and formats associated with a campaign
        $breakdown = [];

        /** @var Collection<int, Collection<Location>> $locationsByNetworkId */
        $locationsByNetworkId = $this->locations->mapToDictionary(fn(Location $location) => [$location->network_id => $location]);

        /** @var Collection<Location> $networkLocations */
        foreach ($locationsByNetworkId as $networkId => $networkLocations) {
            /** @var Collection<int, Location> $locationsByFormatId */
            $locationsByFormatId = collect($networkLocations)->mapToDictionary(fn(Location $location) => [$location->getRelationValue("pivot")->format_id, $location]);

            foreach ($locationsByFormatId as $formatId => $formatLocations) {
                $breakdown[] = new ExternalCampaignDefinition(
                    campaign_id: $this->getKey(),
                    network_id: $networkId,
                    format_id: $formatId,
                    locations: collect($formatLocations),
                );
            }
        }

        return $breakdown;
    }

    /**
     * Get the external resource matching the given parameters
     *
     * @param int $broadcasterId
     * @param int $networkId
     * @param int $formatId
     * @return ExternalResource|null
     */
    public function getExternalRepresentation(int $broadcasterId, int $networkId, int $formatId): ExternalResource|null {
        return $this->external_representations->filter(function (ExternalResource $resource) use ($formatId, $networkId, $broadcasterId) {
            return $resource->broadcaster_id === $broadcasterId &&
                $resource->data->network_id === $networkId &&
                in_array($formatId, $resource->data->formats_id, true);
        })->first();
    }
}
