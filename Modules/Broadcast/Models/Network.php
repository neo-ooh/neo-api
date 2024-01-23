<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Network.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\StructuredColumns\NetworkSettings;

/**
 * @property int                          $id
 * @property string                       $uuid
 * @property int                          $connection_id
 * @property string                       $name
 * @property string                       $slug
 * @property string                       $color
 * @property NetworkSettings              $settings
 * @property Date|null                    $last_sync_at
 * @property Date                         $created_at
 * @property Date                         $updated_at
 * @property Date                         $deleted_at
 *
 * @property BroadcasterConnection        $broadcaster_connection
 * @property Collection<NetworkContainer> $containers
 * @property Collection<Location>         $locations
 *
 * @property string                       $toned_down_color
 *
 * @mixin Builder
 */
class Network extends Model {
	use SoftDeletes;
	use HasPublicRelations;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'networks';

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		"settings"     => NetworkSettings::class,
		"last_sync_at" => "datetime",
	];

	/**
	 * @returns array<string, string|callable>
	 */
	protected function getPublicRelations() {
		return [
			"connection" => "broadcaster_connection",
			"locations"  => "locations",
			"containers" => "containers",
			"products"   => fn(Network $n) => $n->locations->append("product_ids"),
		];
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return BelongsTo<BroadcasterConnection, Network>
	 */
	public function broadcaster_connection(): BelongsTo {
		return $this->belongsTo(BroadcasterConnection::class, "connection_id")->orderBy("name");
	}

	/**
	 * @return HasMany<NetworkContainer>
	 */
	public function containers(): HasMany {
		return $this->hasMany(NetworkContainer::class, "network_id", "id");
	}

	/**
	 * @return HasMany<Location>
	 */
	public function locations(): HasMany {
		return $this->hasMany(Location::class, 'network_id', 'id')->orderBy("name");
	}

	/**
	 * @return HasMany<Format>
	 */
	public function formats() {
		return $this->hasMany(Format::class, "network_id", "id");
	}
}
