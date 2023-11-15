<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlightParameters.php
 */

namespace Neo\Modules\Properties\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;
use Spatie\LaravelData\Optional;

class ContractFlightParameters extends JSONDBColumn {
	public function __construct(
		/**
		 * @var array{property_id: int, impressions: int}|Optional List of properties targeted, for mobile flight
		 */
		public array|Optional  $mobile_properties,

		public int|Optional    $mobile_product,

		/**
		 * @var string|Optional Geographic targeting, for mobile flights
		 */
		public string|Optional $mobile_audience_targeting,

		/**
		 * @var string|Optional Audience targeting, for mobile flights
		 */
		public string|Optional $mobile_additional_targeting,

		public bool|Optional   $mobile_website_retargeting,

		public bool|Optional   $mobile_online_conversion_monitoring,

		public bool|Optional   $mobile_retail_conversion_monitoring,
	) {
	}
}
