<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledFlightsUpdater.php
 */

namespace Neo\Resources\CampaignPlannerPlan\Utils;

use Carbon\Carbon;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class CPCompiledFlightsUpdater implements Cast {
	public function cast(DataProperty $property, mixed $value, array $context): mixed {
		return collect($value)->map(fn($v) => $this->parseFlight($v));
	}

	public function parseFlight(array $flight): CPCompiledFlight {
		if (!array_key_exists("order", $flight)) {
			$flight["order"] = 0;
		}

		if (!array_key_exists("is_compiled", $flight)) {
			$flight["is_compiled"] = true;
		}

		if (!array_key_exists("updated_at", $flight)) {
			$flight["updated_at"] = Carbon::now();
		}

		return CPCompiledFlight::from([
			                              ...$flight,
			                              "start_date" => Carbon::parse($flight["start_date"]),
			                              "end_date"   => Carbon::parse($flight["end_date"]),
			                              "updated_at" => Carbon::parse($flight["end_date"]),
			                              "rawFlight"  => $flight,
		                              ]);
	}
}
