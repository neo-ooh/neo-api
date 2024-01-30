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

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\City;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyTrafficSettings;
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

		/*		$properties = Property::query()->join("actors_details", "actors_details.id", "=", "properties_view.actor_id")
									  ->where("actors_details.parent_id", "=", 4211)->get();
				$properties->load("address");

				foreach ($properties as $property) {
					$addr = $property->address;
					$lng  = $addr->geolocation->latitude;
					$lat  = $addr->geolocation->longitude;

					$addr->geolocation->longitude = $lng;
					$addr->geolocation->latitude  = $lat;
					$addr->save();
				}*/

        $propertiesTraffic = PropertyTrafficSettings::query()->where("format", "=", TrafficFormat::MonthlyAdjusted->value)->get();

        /** @var PropertyTrafficSettings $propertyTraffic */
        foreach($propertiesTraffic as $propertyTraffic) {
            (new EstimateWeeklyTrafficFromMonthJob($propertyTraffic->property_id, 2019, 1))->handle();
            (new EstimateWeeklyTrafficFromMonthJob($propertyTraffic->property_id, 2021, 1))->handle();
            (new EstimateWeeklyTrafficFromMonthJob($propertyTraffic->property_id, 2022, 1))->handle();
            (new EstimateWeeklyTrafficFromMonthJob($propertyTraffic->property_id, 2023, 1))->handle();
        }
	}
}
