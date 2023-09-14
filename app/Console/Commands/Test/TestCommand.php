<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Models\Utils\ActorsGetter;
use Neo\Modules\Properties\Models\OpeningHours;
use Neo\Modules\Properties\Models\Property;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		// -- Deactivate ad copies on BroadSign
//		/** @var BroadSignAdapter $broadsign */
//		$broadsign = BroadcasterAdapterFactory::makeForBroadcaster(1);
//		$client    = new BroadSignClient($broadsign->getConfig());
//
//		$creatives = collect(Creative::inContainer($client, 455721438));
//		$creatives = $creatives->where("active", "=", true);
//		$creatives = $creatives->sortBy("id");
//
//		/** @var Creative $creative */
//		foreach ($creatives as $creative) {
//			if (!$creative->active) {
//				continue;
//			}
//
//			$this->output->writeLn("[" . $creative->id . "] " . $creative->name);
//
//			$r = DB::select("
//				SELECT * FROM `external_resources`
//				WHERE JSON_VALUE(`data`, '$.external_id') = ?
//				AND `deleted_at` IS NULL
//			", [$creative->id]);
//
//			if (count($r) > 0) {
//				$this->output->success("Still alive");
//				continue;
//			}
//
//			$creative->active = false;
//			$creative->save();
//			$this->output->error("Not used anymore, deactivated.");
//		}

		$actors     = ActorsGetter::from(88)->selectChildren(recursive: true)->getSelection();
		$properties = Property::query()->whereIn("actor_id", $actors)
		                      ->whereHas("opening_hours", null, "<", 7)
		                      ->with("opening_hours")
		                      ->get();

		/** @var Property $property */
		foreach ($properties as $property) {
			$this->info($property->name);
			for ($weekday = 1; $weekday <= 7; $weekday++) {
				$hours = $property->opening_hours->firstWhere("weekday", "===", $property);

				if ($hours) {
					$this->comment($weekday . ": OK");
					continue;
				}

				OpeningHours::query()->insert([
					                              "property_id" => $property->getKey(),
					                              "weekday"     => $weekday,
					                              "is_closed"   => false,
					                              "open_at"     => "00:00:00",
					                              "close_at"    => "23:59:00",
				                              ]);
				$this->comment($weekday . ": Added");
			}
		}
	}
}
