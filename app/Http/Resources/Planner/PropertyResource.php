<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyResource.php
 */

namespace Neo\Http\Resources\Planner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Modules\Properties\Models\Property;

/** @mixin Property */
class PropertyResource extends JsonResource {
	public function toArray(Request $request): array {
		return [
			"id"                          => $this->actor_id,
			"is_sellable"                 => true,
			"name"                        => $this->actor->name,
			"address"                     => $this->address->makeHidden(["created_at", "updated_at"]),
			"mobile_impressions_per_week" => $this->mobile_impressions_per_week,
			"network_id"                  => $this->network_id,
			"pricelist_id"                => $this->pricelist_id,
			"translations"                => $this->translations->makeHidden(["created_at", "updated_at"]),
			"website"                     => $this->website,
			"has_tenants"                 => $this->has_tenants,
			"tags"                        => $this->actor->tags,
			"cover_picture_id"            => $this->cover_picture_id,
			"type_id"                     => $this->type_id,
		];
	}
}
