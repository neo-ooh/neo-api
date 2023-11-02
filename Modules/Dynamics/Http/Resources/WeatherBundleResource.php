<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleResource.php
 */

namespace Neo\Modules\Dynamics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Modules\Dynamics\Models\WeatherBundle;

/** @mixin WeatherBundle */
class WeatherBundleResource extends JsonResource {
	public function toArray(Request $request): array {
		return [
			'id'                   => $this->id,
			'name'                 => $this->name,
			'layout'               => $this->layout,
			'background_selection' => $this->background_selection,

			'backgrounds' => WeatherBundleBackgroundResource::collection($this->whenLoaded('backgrounds')),
		];
	}
}
