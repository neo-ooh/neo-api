<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CacheProductsAvailabilitiesSubJob.php
 */

namespace Neo\Modules\Properties\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Neo\Http\Controllers\AvailabilitiesController;

class CacheProductsAvailabilitiesSubJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(public Collection $products, public int $year) {
	}

	public function handle(): void {
		// We want to cache the availabilities for all products for the current year and the next
		$controller = new AvailabilitiesController();
		$controller->getAvailabilitiesForYear($this->products, $this->year);
	}
}
