<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledOOHFlight.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH;

use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledGroup;
use Neo\Resources\FlightType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledOOHFlight extends Data {
	public float $discounted_media_value;
	public float $production_cost_value;

	public function __construct(
		public string         $id,
		public string|null    $name,
		public FlightType     $type,
		public int            $order,

		public string         $start_date,
		public string         $end_date,
		public string         $start_time,
		public string         $end_time,
		public int            $weekdays,

		public int            $traffic,
		public int            $faces_count,
		public float          $impressions,
		public float          $media_value,

		public float          $price,
		public float          $cpm,
		public float          $cpmPrice,

		#[DataCollectionOf(CPCompiledOOHProperty::class)]
		public DataCollection $properties,

		#[DataCollectionOf(CPCompiledGroup::class)]
		public DataCollection $groups = new DataCollection(CPCompiledGroup::class, []),

		public string         $version = "0.1",
	) {
		$this->discounted_media_value = $this->media_value + ($this->media_value * ($this->properties->toCollection()
		                                                                                             ->sum("discount_amount") / 100));
		$this->production_cost_value  = 0;
	}
}
