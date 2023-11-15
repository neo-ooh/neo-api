<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FlightDefinition.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Support\Collection;
use Neo\Modules\Properties\Models\StructuredColumns\ContractFlightParameters;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileProperty;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProduct;
use Neo\Resources\FlightType;

class FlightDefinition {
	public ContractFlightParameters $parameters;

	/**
	 * @param string                                                    $name
	 * @param string                                                    $uid
	 * @param FlightType                                                $type
	 * @param string                                                    $start_date
	 * @param string                                                    $end_date
	 * @param Collection<CPCompiledOOHProduct|CPCompiledMobileProperty> $plan_lines
	 * @param Collection                                                $product_ids
	 * @param Collection                                                $lines
	 * @param bool                                                      $additionalLinesAdded
	 * @param bool                                                      $missingReferencedLine
	 */
	public function __construct(
		public string     $name,
		public string     $uid,
		public FlightType $type,
		public string     $start_date,
		public string     $end_date,
		public Collection $plan_lines = new Collection(),
		public Collection $product_ids = new Collection(),
		public Collection $lines = new Collection(),
		public bool       $additionalLinesAdded = false,
		public bool       $missingReferencedLine = false,
	) {
		$this->parameters = ContractFlightParameters::from([]);
	}
}
