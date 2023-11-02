<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleBackgroundResource.php
 */

namespace Neo\Modules\Dynamics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Modules\Dynamics\Models\WeatherBundleBackground;

/** @mixin WeatherBundleBackground */
class WeatherBundleBackgroundResource extends JsonResource {
	public function toArray(Request $request): array {
		return [
			'id'        => $this->id,
			'bundle_id' => $this->bundle_id,
			'weather'   => $this->weather,
			'period'    => $this->period,
			'uid'       => $this->uid,
			'url'       => $this->url,
		];
	}
}
