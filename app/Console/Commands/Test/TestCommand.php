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
use Illuminate\Support\Facades\DB;
use Neo\Models\Actor;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		dd(Actor::query()->find(3962)->direct_children->count());

		$reader    = new Csv();
		$xlsx      = $reader->load("/Users/vdufois/Documents/Mobile/Drako/NeoFitnessDrakoImpressions.csv");
		$worksheet = $xlsx->getActiveSheet();
		$worksheet->toArray();

		$data = $worksheet->toArray();
		array_shift($data);

		foreach ($data as $k => $row) {
			$propertyId  = (int)$row[0];
			$impressions = (int)$row[10];
			dump($k . "- (" . $propertyId . ") " . $impressions);
			DB::table("properties")
			  ->where("actor_id", "=", $propertyId)
			  ->update(["mobile_impressions_per_week" => round($impressions / 4)]);
		}
	}
}
