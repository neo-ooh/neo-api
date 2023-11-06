<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - City.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Modules\Properties\Services\Resources\City as CityResource;

/**
 * Class City
 *
 * @package Neo\Models
 * @property string      $name
 * @property int|null    $market_id
 * @property int         $province_id
 * @property Point       $geolocation
 *
 * @property Province    $province
 * @property Market|null $market
 *
 * @property int         $id
 */
class City extends Model {
	protected $table = "cities";

	protected $primaryKey = "id";

	public $timestamps = false;

	protected $casts = [
		"geolocation" => Point::class,
	];

	protected $with = [
		"market",
		"province.country",
	];

	protected $fillable = [
		"name",
		"market_id",
		"province_id",
	];

	/**
	 * @return BelongsTo
	 */
	public function province(): BelongsTo {
		return $this->belongsTo(Province::class, "province_id");
	}

	/**
	 * @return BelongsTo
	 */
	public function market(): BelongsTo {
		return $this->belongsTo(Market::class, "market_id");
	}

	/**
	 * @return HasMany
	 */
	public function addresses(): HasMany {
		return $this->hasMany(Address::class, "city_id", "id");
	}

	/**
	 * @return CityResource
	 */
	public function toInventoryResource(): CityResource {
		return new CityResource(
			name         : $this->name,
			province_slug: strtoupper($this->province->slug),
		);
	}
}
