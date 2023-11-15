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
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;

class TestCast implements Cast {
	public function cast(DataProperty $property, mixed $value, array $context): mixed {
		if (is_array($value)) {
			return $value;
		}

		return [$value];
	}
}

class TestDTO extends Data {
	public function __construct(
		#[MapInputName('odoo')]
		#[WithCast(TestCast::class)]
		public array $contract
	) {
	}
}

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
//		$xlsx      = $reader->load("/Users/vdufois/Documents/Mobile/Drako/NeoShoppingDrakoImpressions.csv");
//		$worksheet = $xlsx->getActiveSheet();
//		$worksheet->toArray();
//
//		$data = $worksheet->toArray();
//		array_shift($data);
//
//		foreach ($data as $k => $row) {
//			$propertyId  = (int)$row[1];
//			$impressions = (int)$row[11];
//			dump($k . "- (" . $propertyId . ") " . $impressions);
//			DB::table("properties")
//			  ->where("actor_id", "=", $propertyId)
//			  ->update(["mobile_impressions_per_week" => round($impressions / 4)]);
//		}

//		$inventory = InventoryAdapterFactory::make(InventoryProvider::find(1));
//		$contract  = $inventory->findContract("NEO-620-23");
//		dump($inventory->getContract($contract->contract_id)->toArray());

//		dump(Contract::query()->find(4609)->stored_plan);

//		$j = new ImportContractDataJob(7452);
//		$j->handle();

		dump(TestDTO::from(["odoo" => "hello"])->toArray());

//		$plan = CampaignPlannerSave::query()->find(4020);
//		dump($plan->getPlan()->getPlan());
	}
}
