<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledMobileFlight.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile;

use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledGroup;
use Neo\Resources\FlightType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledMobileFlight extends Data {
	public function __construct(
		public string         $id,
		public string|null    $name,
		public FlightType     $type,
		public int            $order,

		public string         $start_date,
		public string         $end_date,

		public int            $product_id,

		public float          $impressions,
		public float          $media_value,
		public float          $price,
		public float          $cpm,

		public string|null    $audience_targeting,
		public string|null    $additional_targeting,
		public string         $website_retargeting,
		public string         $online_conversion_monitoring,
		public string         $retail_conversion_monitoring,

		public string         $version,

		#[DataCollectionOf(CPCompiledMobileProperty::class)]
		public DataCollection $properties,

		#[DataCollectionOf(CPCompiledGroup::class)]
		public DataCollection $groups,

		#[DataCollectionOf(CPMobileRetailLocation::class)]
		public DataCollection $retail_locations_list,

		public string|null    $landing_page,
	) {
	}
}
