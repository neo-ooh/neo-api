<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledOOHCategory.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledOOHCategory extends Data {
	public float $discounted_media_value;

	public function __construct(
		public int            $id,

		public int            $property_id,

		#[DataCollectionOf(CPCompiledOOHProduct::class)]
		public DataCollection $products,

		public float          $impressions,
		public float          $faces_count,
		public float          $media_value,

		public float          $price,
		public float          $cpm,
		public float          $cpmPrice,

		public bool           $isDiscounted,
		public float          $discount_amount,
		public bool           $hasDiscountError,

		public float          $production_cost_value = 0,
	) {
		$this->discounted_media_value = $this->media_value + ($this->media_value * ($this->discount_amount / 100));
	}
}
