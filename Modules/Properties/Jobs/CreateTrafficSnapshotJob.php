<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateTrafficSnapshotJob.php
 */

namespace Neo\Modules\Properties\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyTrafficSnapshot;

/**
 * Computes the rolling weekly traffic for all properties, and stores it in the DB
 */
class CreateTrafficSnapshotJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct() {
	}

	public function handle() {
		$properties = Property::query()->with(["traffic", "traffic.weekly_data"])->lazy(100);
		$now        = Carbon::now()->toDateString();

		/** @var Property $property */
		foreach ($properties as $property) {
			dump($property->actor->name);
			$traffic = $property->traffic->getRollingWeeklyTraffic();
			PropertyTrafficSnapshot::query()->updateOrInsert([
				                                                 "property_id" => $property->getKey(),
				                                                 "date"        => $now,
			                                                 ], [
				                                                 "traffic" => json_encode($traffic, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT),
			                                                 ]);
		}
	}
}
