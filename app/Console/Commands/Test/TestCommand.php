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
use Neo\Jobs\MatchCityWithMarketJob;
use Neo\Jobs\PullCityGeolocationJob;
use Neo\Models\City;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		// DO NOT DELETE - DRAKO IMPRESSIONS IMPORTER
//		$reader    = new Csv();
//		$xlsx      = $reader->load("/Users/vdufois/Documents/Mobile/Drako/NeoFitnessDrakoImpressions.csv");
//		$worksheet = $xlsx->getActiveSheet();
//		$worksheet->toArray();
//
//		$data = $worksheet->toArray();
//		array_shift($data);
//
//		foreach ($data as $k => $row) {
//			$propertyId  = (int)$row[0];
//			$impressions = (int)$row[10];
//			dump($k . "- (" . $propertyId . ") " . $impressions);
//			DB::table("properties")
//			  ->where("actor_id", "=", $propertyId)
//			  ->update(["mobile_impressions_per_week" => round($impressions / 4)]);
//		}


		$cities = City::query()->whereNull("market_id")->get();
		dump($cities->count());

		/** @var City $city */
		foreach ($cities as $city) {
			dump($city->name);
			if ($city->geolocation === null) {
				PullCityGeolocationJob::dispatch($city->getKey())
				                      ->chain([new MatchCityWithMarketJob($city->getKey())]);

				continue;
			}

			MatchCityWithMarketJob::dispatch($city->getKey());
		}


//
//		$inventory = InventoryAdapterFactory::make(InventoryProvider::find(1));
//		$contract  = $inventory->findContract("NEO-620-23");
//		dump($inventory->getContract($contract->contract_id)->toArray());

//		dump(Contract::query()->find(4609)->stored_plan);

//		$j = new ImportContractDataJob(7452);
//		$j->handle();

//		Actor::find(4209)->moveTo(Actor::find(1));

//		$plan = CampaignPlannerSave::query()->find(4020);
//		dump($plan->getPlan()->getPlan());
	}
}
