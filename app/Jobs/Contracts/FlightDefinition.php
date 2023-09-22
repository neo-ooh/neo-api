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
use Neo\Resources\Contracts\CPCompiledProduct;
use Neo\Resources\Contracts\FlightType;

class FlightDefinition {
	/**
	 * @param string                        $name
	 * @param string                        $uid
	 * @param FlightType                    $type
	 * @param string                        $startDate
	 * @param string                        $endDate
	 * @param Collection<CPCompiledProduct> $planLines
	 */
	public function __construct(
		public string     $name,
		public string     $uid,
		public FlightType $type,
		public string     $startDate,
		public string     $endDate,
		public Collection $planLines = new Collection(),
		public Collection $productIds = new Collection(),
		public Collection $lines = new Collection(),
		public bool       $additionalLinesAdded = false,
		public bool       $missingReferencedLine = false,
	) {
	}
}
