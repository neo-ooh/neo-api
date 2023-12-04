<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportMobilePropertiesCommand.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Enums\ActorType;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyNetwork;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class ImportMobilePropertiesCommand extends Command {
	protected $signature = 'mobile:import-properties {--network=} {--parent=} {--inventory=} {--property-type=} {file}';

	protected $description = 'Import mobile properties';

	public function handle(): void {
		$reader    = new Csv();
		$file      = $reader->load($this->argument("file"));
		$worksheet = $file->getActiveSheet()->getRowIterator();

		$parent = Actor::query()->where("id", "=", $this->option("parent"))->where("is_group", "=", true)->first();
		if (!$parent || $parent->type !== ActorType::Group) {
			dd("Invalid actor");
		}

		$network = PropertyNetwork::query()
		                          ->where("mobile_sales", "=", true)
		                          ->where("id", "=", $this->option("network"))
		                          ->first();
		if (!$network) {
			dd("Invalid network");
		}
		$networkId = $network->getKey();

		$inventory = InventoryProvider::query()->where("id", "=", $this->option("inventory"))->first();
		if (!$inventory) {
			dd("Invalid Inventory");
		}
		$inventoryId = $inventory->getKey();

		$provinces = Province::query()->get();

		foreach ($worksheet as $k => $row) {
			dump($k);
			if ($k === 1) {
				continue;
			}

			$actor           = new Actor();
			$actor->locale   = "en";
			$actor->is_group = true;

			$property                 = new Property();
			$property->network_id     = $networkId;
			$property->last_review_at = Date::now();
			$property->type_id        = $this->option("property-type");

			$address              = new Address();
			$address->geolocation = new Point(0, 0);
			$address->zipcode     = "";

			$cityName     = null;
			$provinceSlug = null;

			$inventoryResource               = new ExternalInventoryResource();
			$inventoryResource->type         = InventoryResourceType::Property;
			$inventoryResource->inventory_id = $inventoryId;
			$inventoryResource->context      = [];

			foreach ($row->getColumnIterator() as $k => $col) {
				switch ($k) {
					case "A":
						$inventoryResource->external_id = $col->getValue();
						break;
					case "B":
						$actor->name = $col->getValue();
						break;
					case "C":
						$address->line_1 = $col->getValue();
						break;
					case "D":
						$cityName = $col->getValue();
						break;
					case "E":
						$provinceSlug = match (trim(explode('/', $col->getValue())[0])) {
							"Ontario"                              => "ON",
							"Québec", "Quebec"                     => "QC",
							"British Columbia", "British-Columbia" => "BC",
							"Nova Scotia"                          => "NS",
							"Northwest Territories"                => "NT",
							"Yukon"                                => "YT",
							"Newfoundland and Labrador"            => "NL",
							"Alberta"                              => "AB",
							"Manitoba"                             => "MB",
							"Bew Brunswick"                        => "NB",
							"Prince Edward Island"                 => "PE",
							"SK"                                   => "Saskatchewan",
							default                                => null
						};
						break;
					case "F":
						$country = $col->getValue() === 'Canada' ? 'CA' : null;
						break;
					case "G":
						$address->zipcode = $col->getValue() ?? "";
						break;
					case "I":
						if ($col->getValue() !== null) {
							$address->geolocation->longitude = $col->getValue();
						}
						break;
					case "J":
						if ($col->getValue() !== null) {
							$address->geolocation->latitude = $col->getValue();
						}
						break;
					case "K":
						$property->mobile_impressions_per_week = (int)round($col->getValue() / 4);
						break;
				}
			}

			if (!$provinceSlug
				|| $address->line_1 === null
				|| $cityName === null
				|| !$address->geolocation->longitude
				|| !$address->geolocation->latitude
			) {
				continue;
			}

			DB::beginTransaction();

			$actor->save();
			$actor->moveTo($parent);

			$province          = $provinces->firstWhere("slug", "=", $provinceSlug);
			$city              = City::query()->firstOrCreate([
				                                                  "name"        => $cityName,
				                                                  "province_id" => $province->getKey(),
			                                                  ]);
			$address->line_2   = "";
			$address->city_id  = $city->getKey();
			$address->timezone = "";
			$address->save();

			$property->actor_id   = $actor->getKey();
			$property->address_id = $address->getKey();
			$property->save();

			$property->traffic()->create([
				                             "start_year" => 2022,
				                             "format"     => TrafficFormat::MonthlyMedian->value,
			                             ]);

			$property->translations()->createMany([
				                                      ["locale" => "fr-CA"],
				                                      ["locale" => "en-CA"],
			                                      ]);

			$inventoryResource->resource_id = $property->inventory_resource_id;
			$inventoryResource->save();

			DB::commit();
		}
	}
}
