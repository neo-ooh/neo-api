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
use Illuminate\Support\LazyCollection;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Hivestack\HivestackAdapter;
use Neo\Modules\Properties\Services\Hivestack\Models\Unit;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
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

//
//		$cities = City::query()->whereNull("market_id")->get();
//		dump($cities->count());
//
//		/** @var City $city */
//		foreach ($cities as $city) {
//			dump($city->name);
//			if ($city->geolocation === null) {
//				PullCityGeolocationJob::dispatch($city->getKey())
//				                      ->chain([new MatchCityWithMarketJob($city->getKey())]);
//
//				continue;
//			}
//
//			MatchCityWithMarketJob::dispatch($city->getKey());
//		}

//		$contracts = Contract::query()->whereHas("flights", function (Builder $query) {
//			$query->whereRaw("`start_date` > `end_date`");
//		})->get();
//
//		foreach ($contracts as $contract) {
//			dump($contract->contract_id);
//			$j = new ImportContractDataJob($contract->getKey());
//			$j->handle();
//		}


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

		/** @var HivestackAdapter $hivestack */
		$hivestack       = InventoryAdapterFactory::make(InventoryProvider::query()->find(4));
		$hivestackClient = $hivestack->getConfig()->getClient();

		$hivestackProducts = LazyCollection::make(function () use ($hivestackClient) {
			$pageSize = 100;
			$cursor   = 0;

			do {
				$units = Unit::all(
					client: $hivestackClient,
					limit : $pageSize,
					offset: $cursor,
				);

				foreach ($units as $unit) {
					yield $unit;
				}

				$cursor += $pageSize;
			} while ($units->count() === $pageSize);
		});

		/** @var Unit $product */
		foreach ($hivestackProducts as $k => $product) {
			dump($product->name, $product->external_id);
			if ($product->external_id === "827997778") {
				dump($product);
				break;
			}
		}
	}
}
