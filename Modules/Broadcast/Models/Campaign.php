<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Illuminate\Support\Facades\DB;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Actor;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CampaignStatus;
use Neo\Modules\Broadcast\Enums\CampaignWarning;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Jobs\Campaigns\DeleteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Campaigns\PromoteCampaignJob;
use Neo\Modules\Broadcast\Models\Structs\CampaignLocation;
use Neo\Modules\Broadcast\Models\Structs\CampaignScheduleLocation;
use Neo\Modules\Broadcast\Models\Structs\CampaignScheduleProduct;
use Neo\Modules\Broadcast\Rules\AccessibleCampaign;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\Campaign as CampaignResource;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ResolvedProduct;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Neo\Models\Campaigns
 *
 * @property int                                  $id
 * @property int                                  $parent_id
 * @property int                                  $creator_id
 * @property int|null                             $flight_id
 * @property string                               $name
 * @property double                               $static_duration_override  Duration in seconds for contents without a
 *           predetermined duration
 *           (pictures). Override the format content length if set to a value above zero
 * @property double                               $dynamic_duration_override Maximum allowed duration for contents with a
 *           predetermined duration
 *           (videos). Override the format content length if set to a value above zero
 * @property int                                  $occurrences_in_loop       Tell how many time in one loop the campaign should
 *           play, default to
 *           1
 * @property int                                  $priority                  Higher number means lower priority
 * @property Date                                 $start_date                Y-m-d
 * @property Date                                 $start_time                H:m:s
 * @property Date                                 $end_date                  Y-m-d
 * @property Date                                 $end_time                  H:m:s
 * @property int                                  $broadcast_days            Bit mask of the days of the week the campaign should
 *           run:
 *           127 =>
 *           01111111 - all week
 * @property int                                  $default_schedule_duration_days
 *
 * @property Date                                 $created_at
 * @property Date                                 $updated_at
 * @property Date|null                            $deleted_at
 *
 * @property CampaignStatus                       $status
 *
 * @property Actor                                $parent
 * @property Actor                                $creator
 *
 * @property int                                  $schedules_count
 * @property Collection<Schedule>                 $schedules
 * @property Collection<Schedule>                 $expired_schedules
 *
 * @property Collection<Location>                 $locations
 * @property int                                  $locations_count
 * @property Collection<ResolvedProduct>          $products
 * @property Collection<CampaignScheduleProduct>  $schedules_products_targeting
 * @property Collection<CampaignScheduleLocation> $schedules_location_targeting
 *
 * @property Collection<CampaignLocation>         $resolved_locations
 *
 * @property Collection<Layout>                   $layouts
 *
 * @property ContractFlight|null                  $flight
 * @property Contract|null                        $contract
 *
 * @mixin Builder<Campaign>
 */
class Campaign extends BroadcastResourceModel {
	use SoftDeletes;
	use HasPublicRelations;
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
		"static_duration_override",
		"dynamic_duration_override",
		"priority",
		"start_date",
		"start_time",
		"end_date",
		"end_time",
		"broadcast_days",
		"default_schedule_duration_days",
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

	/**
	 * The rule used to validate access to the model upon binding it with a route
	 *
	 * @var string
	 */
	protected string $accessRule = AccessibleCampaign::class;

	protected function getPublicRelations() {
		return [
			"contract"                           => "contract",
			"creator"                            => "creator",
			"expired_schedules"                  => ["expired_schedules.owner:id,name"],
			"expired_schedules_contents"         => Relation::make(
				load: ["expired_schedules.owner:id,name", "expired_schedules.contents"]
			),
			"external_representations"           => Relation::make(
				load: "external_representations",
				gate: Capability::dev_tools,
			),
			"flight"                             => "flight",
			"formats"                            => ["formats.layouts.frames", "formats.loop_configurations"],
			"locations"                          => "locations",
			"parent"                             => "parent",
			"performances"                       => "performances",
			"products"                           => "products",
			"shares"                             => "shares",
			"schedules"                          => Relation::make(
				load: "schedules.owner:id,name"
			),
			"schedules_count"                    => Relation::make(
				count: "schedules"
			),
			"schedules_contents"                 => Relation::make(
				load: ["schedules.owner:id,name", "schedules.contents"]
			),
			"schedules_external_representations" => Relation::make(
				load: "schedules.external_representations",
				gate: Capability::dev_tools,
			),
			"schedules_targeting"                => Relation::make(
				append: ["schedules_products_targeting", "schedules_locations_targeting"],
				gate  : Capability::campaigns_edit,
			),
			"status"                             => "append:status",
			"tags"                               => Relation::make(
				load: "broadcast_tags",
				gate: Capability::campaigns_tags,
			),
		];
	}

	protected static function boot(): void {
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
		return $this->belongsTo(Actor::class, 'parent_id', 'id')->withTrashed();
	}

	/**
	 * @return BelongsTo<Actor, Campaign>
	 */
	public function creator(): BelongsTo {
		return $this->belongsTo(Actor::class, 'creator_id', 'id');
	}

	/**
	 * @return HasMany<Schedule>
	 */
	public function schedules(): HasMany {
		return $this->hasMany(Schedule::class, "campaign_id", "id")
		            ->where(function (Builder $query) {
			            $query->where("end_date", ">=", Carbon::now()->startOfDay())
			                  ->orWhereRelation("details", function (Builder $query) {
				                  $query->where("is_approved", "=", false);
			                  })
			                  ->where("is_archived", "=", false);
		            })
		            ->orderBy("order");
	}

	/**
	 * @return HasMany<Schedule>
	 */
	public function expired_schedules(): HasMany {
		return $this->hasMany(Schedule::class, "campaign_id", "id")
		            ->whereRelation("details", function (Builder $query) {
			            $query->where("is_approved", "=", true);
		            })
		            ->where("end_date", "<", Carbon::now()->startOfDay())
		            ->orWhere("is_archived", "=", true)
		            ->withTrashed()
		            ->orderByDesc("end_date");
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
	 * @return BelongsToMany<ResolvedProduct>
	 */
	public function products(): BelongsToMany {
		return $this->belongsToMany(ResolvedProduct::class, "campaign_products", "campaign_id", "product_id")
		            ->withTimestamps();
	}

	/**
	 * @return BelongsToMany<Format>
	 */
	public function formats(): BelongsToMany {
		return $this->belongsToMany(Format::class, "campaign_formats", "campaign_id", "format_id")
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

	public function flight(): BelongsTo {
		return $this->belongsTo(ContractFlight::class, "flight_id", "id");
	}

	public function contract(): HasOneDeep {
		return $this->hasOneDeepFromRelations([$this->flight(), (new ContractFlight())->contract()]);
	}


	/*
	|--------------------------------------------------------------------------
	| Attributes
	|--------------------------------------------------------------------------
	*/

	public function getStatusAttribute(): CampaignStatus {
		// Is the campaign expired ?
		if ($this->end_date->isBefore(Date::now())) {
			return CampaignStatus::Expired;
		}

		if ($this->schedules->count() === 0) {
			return CampaignStatus::Empty;
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

	/**
	 * @return Collection
	 */
	public function getResolvedLocationsAttribute() {
		/** @var Collection<CampaignLocation> $productsLocations */
		$productsLocations = $this->products()
		                          ->get()
		                          ->load(["locations", "format"])
		                          ->flatMap(fn(Product $product) => $product->locations->map(fn(Location $location) => (
		                          new CampaignLocation(
			                          location_id: $location->getKey(),
			                          format_id  : $product->format_id,
			                          network_id : $location->network_id,
			                          location   : $location,
		                          ))));

		/** @var Collection<CampaignLocation> $locations */
		$locations = $this->locations->map(fn(Location $location) => (
		new CampaignLocation(
			location_id: $location->getKey(),
			format_id  : $location->getRelationValue("pivot")->format_id,
			network_id : $location->network_id,
			location   : $location,
		)
		));

		return $productsLocations->merge($locations)->unique(fn(CampaignLocation $cl) => $cl->location_id . "-" . $cl->format_id);
	}

	public function getSchedulesProductsTargetingAttribute() {
		return CampaignScheduleProduct::collection(
			DB::select("
				WITH `capro` AS (SELECT `campaign_products`.`campaign_id`, `p`.*
                     FROM `campaign_products`
                          JOIN `products_view` `p` ON `campaign_products`.`product_id` = `p`.`id`)
				SELECT DISTINCT `capro`.`campaign_id` AS `campaign_id`,
				                `capro`.`id` AS `product_id`,
				                `s`.`id` AS `schedule_id`
				  FROM `capro`
				       CROSS JOIN `schedules` `s` ON `capro`.`campaign_id` = `s`.`campaign_id`
				       JOIN `schedule_contents` `sc` ON `s`.`id` = `sc`.`schedule_id`
				       JOIN `contents` `c` ON `sc`.`content_id` = `c`.`id`
				       JOIN `layouts` `l` ON `c`.`layout_id` = `l`.`id`
				       JOIN `format_layouts` `fl` ON `l`.`id` = `fl`.`layout_id` AND `capro`.`format_id` = `fl`.`format_id`
				 WHERE `capro`.`campaign_id` = ?
				   AND `s`.`deleted_at` IS NULL
				   AND `capro`.`format_id` NOT IN (SELECT `scdf`.`format_id`
				                                     FROM `schedule_content_disabled_formats` `scdf`
				                                     WHERE `scdf`.`schedule_content_id` = `sc`.`id`)
			", [$this->getKey()])
		)->toCollection();
	}

	public function getSchedulesLocationsTargetingAttribute() {
		return CampaignScheduleLocation::collection(
			DB::select("
			  WITH `calo` AS (SELECT `campaign_locations`.*, `locations`.`name`
			                     FROM `campaign_locations`
			                     JOIN `locations` ON `campaign_locations`.`location_id` = `locations`.`id`)
			SELECT DISTINCT `calo`.`campaign_id`,
			                `calo`.`location_id`,
			                `s`.`id` as `schedule_id`
			  FROM `calo`
			       CROSS JOIN `schedules` `s` ON `calo`.`campaign_id` = `s`.`campaign_id`
			       JOIN `schedule_contents` `sc` ON `s`.`id` = `sc`.`schedule_id`
			       JOIN `contents` `c` ON `sc`.`content_id` = `c`.`id`
			       JOIN `layouts` `l` ON `c`.`layout_id` = `l`.`id`
			       JOIN `format_layouts` `fl` ON `l`.`id` = `fl`.`layout_id` AND `calo`.`format_id` = `fl`.`format_id`
			 WHERE `calo`.`campaign_id` = ?
				   AND `s`.`deleted_at` IS NULL
			   AND `calo`.`format_id` NOT IN (SELECT `scdf`.`format_id`
			                                     FROM `schedule_content_disabled_formats` `scdf`
			                                     WHERE `scdf`.`schedule_content_id` = `sc`.`id`)
			", [$this->getKey()])
		)->toCollection();
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
		// Check if the campaign already has a pending job, bail out if so.
		if ($this->broadcast_jobs()->where("type", "=", PromoteCampaignJob::TYPE->value)
		         ->where("status", "<>", BroadcastJobStatus::Active)
		         ->whereNull("last_attempt_at")
		         ->exists()) {
			return;
		}

		new PromoteCampaignJob($this->getKey());
	}


	/*
	|--------------------------------------------------------------------------
	| Resources
	|--------------------------------------------------------------------------
	*/

	/**
	 * @param int|null $broadcasterId required to populate `advertiser` property
	 * @return CampaignResource
	 */
	public function toResource(int|null $broadcasterId = null): CampaignResource {
		$this->loadMissing("parent");

		return new CampaignResource(
			enabled            : true,
			name               : $this->parent->name . "_" . $this->name,
			start_date         : $this->start_date->toDateString(),
			start_time         : $this->start_time->toTimeString(),
			end_date           : $this->end_date->toDateString(),
			end_time           : $this->end_time->toTimeString(),
			broadcast_days     : $this->broadcast_days,
			priority           : $this->priority,
			occurrences_in_loop: $this->occurrences_in_loop,
			advertiser         : $broadcasterId ? $this->contract?->advertiser?->getExternalRepresentation($broadcasterId) : null,
		);
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
		//  2. Format

		// This is done using the list of locations and formats associated with a campaign
		$breakdown = [];

		/** @var Collection<int, Collection<CampaignLocation>> $locationsByNetworkId */
		$locationsByNetworkId = $this->resolved_locations->mapToDictionary(fn(CampaignLocation $location) => [$location->network_id => $location]);

		/** @var Collection<CampaignLocation> $networkLocations */
		foreach ($locationsByNetworkId as $networkId => $networkLocations) {
			/** @var Collection<int, Location> $locationsByFormatId */
			$locationsByFormatId = collect($networkLocations)->mapToDictionary(fn(CampaignLocation $location) => [$location->format_id => $location->location]);

			foreach ($locationsByFormatId as $formatId => $formatLocations) {
				$breakdown[] = new ExternalCampaignDefinition(
					campaign_id: $this->getKey(),
					network_id : $networkId,
					format_id  : $formatId,
					locations  : ExternalBroadcasterResourceId::collection(array_map(static fn(Location $location) => $location->toExternalBroadcastIdResource(), $formatLocations)),
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

	/*
	|--------------------------------------------------------------------------
	| Warnings
	|--------------------------------------------------------------------------
	*/

	public function getWarningsAttribute(): array {
		$warnings = [];

		if ($this->locations()->count() === 0) {
			$warnings[CampaignWarning::NoLocations->value] = [];
		}

		return $warnings;
	}
}
