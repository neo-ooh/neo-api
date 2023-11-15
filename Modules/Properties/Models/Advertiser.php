<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Advertiser.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Neo\Modules\Properties\Models\AdvertiserRepresentation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

/**
 * @property int    $id
 * @property string $name
 * @property int    $odoo_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Advertiser extends Model {
	use HasPublicRelations;

	protected $table = "advertisers";

	protected $primaryKey = "id";

	protected $fillable = [
		"name",
		"odoo_id",
	];

	protected array $publicRelations = [
		"representations" => "representations",
	];

	/**
	 * @return HasMany<AdvertiserRepresentation>
	 */
	public function representations(): HasMany {
		return $this->hasMany(AdvertiserRepresentation::class, "advertiser_id", "id");
	}

	/**
	 * @param int $broadcasterId
	 * @return ExternalBroadcasterResourceId|null
	 */
	public function getExternalRepresentation(int $broadcasterId): ExternalBroadcasterResourceId|null {
		/** @var AdvertiserRepresentation|null $representation */
		$representation = $this->representations()->where("broadcaster_id", "=", $broadcasterId)->first();

		if (!$representation) {
			return null;
		}

		return new ExternalBroadcasterResourceId(
			broadcaster_id: $broadcasterId,
			external_id   : $representation->external_id,
			type          : ExternalResourceType::Advertiser,
		);
	}
}
