<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyNetwork.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                  $id
 * @property string               $name
 * @property string               $slug
 * @property string               $color
 * @property bool                 $ooh_sales
 * @property bool                 $mobile_sales
 *
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon|null          $deleted_at
 *
 *
 * @property Collection<Property> $properties
 * @property Collection<Field>    $properties_fields
 */
class PropertyNetwork extends Model {
	use HasTimestamps;
	use SoftDeletes;
	use HasPublicRelations;

	protected $table = "properties_networks";

	protected $casts = [
		"ooh_sales"    => "boolean",
		"mobile_sales" => "boolean",
	];

	/**
	 * @returns array<string, string|callable>
	 */
	protected function getPublicRelations() {
		return [
			"fields" => "properties_fields",
		];
	}


	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return HasMany<Property>
	 */
	public function properties(): HasMany {
		return $this->hasMany(Property::class, "network_id", "id")->orderBy("name");
	}

	/**
	 * @return BelongsToMany<Field>
	 */
	public function properties_fields(): BelongsToMany {
		return $this->belongsToMany(Field::class, "fields_networks", "network_id", "field_id")
		            ->withPivot(["order"])
		            ->orderByPivot("order");
	}
}
