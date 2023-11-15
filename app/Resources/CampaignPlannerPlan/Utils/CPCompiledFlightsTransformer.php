<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledFlightsTransformer.php
 */

namespace Neo\Resources\CampaignPlannerPlan\Utils;

use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class CPCompiledFlightsTransformer implements Transformer {
	public function transform(DataProperty $property, mixed $value): mixed {
		return array_map(fn(CPCompiledFlight $flight) => $flight->rawFlight, collect($value)->all());
	}
}
