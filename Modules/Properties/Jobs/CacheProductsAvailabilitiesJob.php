<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CacheProductsAvailabilitiesJob.php
 */

namespace Neo\Modules\Properties\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Http\Controllers\AvailabilitiesController;
use Neo\Modules\Properties\Models\Product;

class CacheProductsAvailabilitiesJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct() {
	}

	public function handle(): void {
		// We want to cache the availabilities for all products for the current year and the next
		Product::query()->chunk(500, function (Collection $products, $page) {
			$controller = new AvailabilitiesController();
			$controller->getAvailabilitiesForYear($products->pluck("id"), Carbon::now()->year);
			$controller->getAvailabilitiesForYear($products->pluck("id"), Carbon::now()->year + 1);
		});
	}
}
