<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Location.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Neo\Models\Actor;
use Neo\Models\ScreenshotRequest;
use Neo\Models\SecuredModel;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Rules\AccessibleLocation;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Resources\BroadcastLocation;
use Neo\Modules\Properties\Services\Resources\BroadcastPlayer;

/**
 * Neo\Models\ActorsLocations
 *
 * @property int                                $id
 * @property int                                $network_id
 * @property string                             $external_id
 * @property int                                $display_type_id
 * @property string                             $name
 * @property string                             $internal_name
 * @property int|null                           $container_id
 * @property string                             $province [QC, ON, ...]
 * @property string                             $city
 * @property boolean                            $scheduled_sleep
 * @property Date                               $sleep_end
 * @property Date                               $sleep_start
 * @property Date                               $created_at
 * @property Date                               $updated_at
 * @property Date|null                          $deleted_at
 *
 * @property ?NetworkContainer                  $container
 * @property Network                            $network
 * @property EloquentCollection<Player>         $players
 * @property DisplayType                        $display_type
 *
 * @property-read Collection<int>               $product_ids
 * @property-read Collection<ScreenshotRequest> $screenshots_requests
 * @property-read EloquentCollection<Product>   $products
 * @property-read EloquentCollection<Actor>     $actors
 *
 * @mixin Builder
 */
class Location extends SecuredModel {
	use SoftDeletes;
	use HasPublicRelations;

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
	protected $table = 'locations';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<string>
	 */
	protected $fillable = [
		"network_id",
		"external_id",
		"display_type_id",
		"name",
		"internal_name",
		"container_id",
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		"scheduled_sleep" => "boolean",
		"sleep_end"       => "datetime",
		"sleep_start"     => "datetime",
	];

	/**
	 * The rule used to validate access to the model upon binding it with a route
	 *
	 * @var class-string
	 */
	protected string $accessRule = AccessibleLocation::class;

	/**
	 * @var array<string, string|callable>
	 */
	protected array $publicRelations = [
		"display_type" => "display_type",
		"network"      => "network",
		"broadcaster"  => "network.broadcaster_connection",
		"players"      => "players",
		"actors"       => "actors",
		"products"     => "products",
		"product_ids"  => "append:product_ids",
	];

	protected $touches = ["products"];


	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return BelongsTo<Network, Location>
	 */
	public function network(): BelongsTo {
		return $this->belongsTo(Network::class, "network_id");
	}

	/**
	 * @return HasMany<Player>
	 */
	public function players(): HasMany {
		return $this->hasMany(Player::class, "location_id");
	}

	/**
	 * @return BelongsTo<DisplayType, Location>
	 */
	public function display_type(): BelongsTo {
		return $this->belongsTo(DisplayType::class, "display_type_id");
	}

	/**
	 * @return BelongsTo<NetworkContainer, Location>
	 */
	public function container(): BelongsTo {
		return $this->belongsTo(NetworkContainer::class, "container_id", "id");
	}

	/**
	 * @return BelongsToMany<Actor>
	 */
	public function actors(): BelongsToMany {
		return $this->belongsToMany(Actor::class, "actors_locations", "location_id", "actor_id");
	}

	/**
	 * @return BelongsToMany<Product>
	 */
	public function products(): BelongsToMany {
		return $this->belongsToMany(Product::class, "products_locations", "location_id", "product_id");
	}

	/**
	 * @return Collection<int>
	 */
	public function getProductIdsAttribute(): Collection {
		return $this->products()->allRelatedIds();
	}

	/* Reports */

	/**
	 * @return HasMany<ScreenshotRequest>
	 */
	public function screenshots_requests(): HasMany {
		return $this->hasMany(ScreenshotRequest::class, "location_id");
	}


	/*
	|--------------------------------------------------------------------------
	| ***
	|--------------------------------------------------------------------------
	*/

	public function loadHierarchy(): self {
		$this->container?->append('parents_list');

		return $this;
	}

	/**
	 * @return ExternalBroadcasterResourceId
	 */
	public function toExternalBroadcastIdResource(): ExternalBroadcasterResourceId {
		return new ExternalBroadcasterResourceId(
			broadcaster_id: $this->network->connection_id,
			external_id   : $this->external_id,
			type          : ExternalResourceType::Location,
		);
	}

	public function toInventoryResource() {
		return new BroadcastLocation(
			provider    : $this->network->broadcaster_connection->broadcaster,
			id          : $this->getKey(),
			external_id : $this->toExternalBroadcastIdResource(),
			name        : $this->name,
			screen_count: $this->players->sum("screen_count"),
			players     : BroadcastPlayer::collection($this->players->map(fn(Player $player) => $player->toInventoryResource())),
		);
	}
}
