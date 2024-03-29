<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Field.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Network;

/**
 * @property int                      $id
 * @property int|null                 $category_id
 * @property int                      $order
 * @property string                   $name_en
 * @property string                   $name_fr
 * @property string                   $type One of 'int', 'float' or 'bool'
 * @property string                   $unit
 * @property bool                     $is_filter
 * @property boolean                  $demographic_filled
 * @property string|null              $visualization
 * @property Carbon                   $created_at
 * @property Carbon                   $update_at
 *
 * @property Collection<FieldSegment> $segments
 * @property Collection<Network>      $networks
 *
 * @property Collection<int>          $network_ids
 */
class Field extends Model {
	use HasPublicRelations;

	protected $primaryKey = "id";

	protected $table = "fields";

	protected $fillable = [
		"category_id",
		"name_en",
		"name_fr",
		"type",
		"unit",
		"is_filter",
		"demographic_filled",
		"visualization",
	];

	protected $casts = [
		"is_filter"          => "boolean",
		"demographic_filled" => "boolean",
	];

	protected $with = ["segments"];

	protected $hidden = ["pivot"];

	public function getPublicRelations(): array {
		return [
			"category" => Relation::make("category"),
			"segments" => Relation::make("segments"),
			"networks" => Relation::make("networks"),
		];
	}

	public function networks(): BelongsToMany {
		return $this->belongsToMany(PropertyNetwork::class, "fields_networks", "field_id", "network_id");
	}

	public function getNetworkIdsAttribute() {
		return $this->networks()->allRelatedIds();
	}

	public function segments(): HasMany {
		return $this->hasMany(FieldSegment::class, "field_id", "id")->orderBy("order");
	}

	public function category(): BelongsTo {
		return $this->belongsTo(FieldsCategory::class, "category_id", "id");
	}
}
