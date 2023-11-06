<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductResource.php
 */

namespace Neo\Http\Resources\Planner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Modules\Properties\Models\Product;

/** @mixin Product */
class ProductResource extends JsonResource {
	public function toArray(Request $request): array {
		return [
			'id'                    => $this->id,
			'name_en'               => $this->name_en,
			'name_fr'               => $this->name_fr,
			'quantity'              => $this->quantity,
			'unit_price'            => $this->unit_price,
			'is_bonus'              => $this->is_bonus,
			'linked_product_id'     => $this->linked_product_id,
			'is_sellable'           => $this->is_sellable,
			'inventory_resource_id' => $this->inventory_resource_id,
			'allowed_media_types'   => $this->allowed_media_types,
			'allows_audio'          => $this->allows_audio,
			'production_cost'       => $this->production_cost,
			'allows_motion'         => $this->allows_motion,
			'screen_size_in'        => $this->screen_size_in,
			'programmatic_price'    => $this->programmatic_price,

			'property_id'      => $this->property_id,
			'category_id'      => $this->category_id,
			'format_id'        => $this->format_id,
			'screen_type_id'   => $this->screen_type_id,
			'site_type_id'     => $this->site_type_id,
			'cover_picture_id' => $this->cover_picture_id,

			'impressions_models' => $this->impressions_models,
		];
	}
}
