<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AssociateProductsWithHivestack.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Support\Facades\App;
use Neo\Documents\XLSX\Worksheet;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Output\ConsoleOutput;

class AssociateProductsWithHivestack extends Job {
	public ConsoleOutput|null $output = null;

	public function __construct(public int $inventoryId) {
		if (App::runningInConsole()) {
			$this->output = new ConsoleOutput();
		}
	}

	protected function run(): mixed {
		$provider = InventoryProvider::query()->find($this->inventoryId);
		/** @var InventoryAdapter $inventory */
		$inventory = $provider->getAdapter();

		$products = $inventory->listProducts();

		$notMatched = [];

		/** @var IdentifiableProduct $unit */
		foreach ($products as $unit) {
			// Ignore disabled products
			if (!$unit->product->is_sellable) {
				continue;
			}

			$this->output?->write("#" . $unit->resourceId->external_id . " " . $unit->product->name[0]->value);

			$location = Location::query()->where("external_id", "=", trim($unit->resourceId->context["external_id"]))->first();

			if (!$location) {
				$this->output?->writeln(": No location found.");
				$notMatched[] = $unit->resourceId->external_id . ":" . $unit->product->name[0]->value;
				continue;
			}

			$location->load("products.property");

			$products = $location->products->where("is_bonus", "=", false);
			if ($products->count() === 0) {
				$this->output?->writeln(": No products found for location.");
				$notMatched[] = $unit->resourceId->external_id . ":" . $unit->product->name[0]->value;
				continue;
			}

			if ($products->count() > 1) {
				$this->output?->write(" (Multiple products found!)");
			}

			/** @var Product $product */
			$product = $products->first();

			// Does this product already has an external representation for this inventory ?
			/** @var ExternalInventoryResource|null $representation */
			$representation = $product->external_representations()->firstWhere("inventory_id", "=", $this->inventoryId);

			if ($representation) {
				// Representation already exist, append current ID
				if (is_array($representation->context->units)) {
					$representation->context->units[$location->getKey()] = [
						"id"   => $unit->resourceId->external_id,
						"name" => $unit->product->name[0]->value,
					];
				} else {
					$representation->context->units = [$location->getKey() => [
						"id"   => $unit->resourceId->external_id,
						"name" => $unit->product->name[0]->value,
					]];
				}
			} else {
				$representation = new ExternalInventoryResource([
					                                                "resource_id"  => $product->inventory_resource_id,
					                                                "inventory_id" => $this->inventoryId,
					                                                "type"         => InventoryResourceType::Product,
					                                                "external_id"  => "MULTIPLE",
					                                                "context"      => [
						                                                "network_id" => $unit->resourceId->context["network_id"],
						                                                "units"      => [
							                                                $location->getKey() => [
								                                                "id"   => $unit->resourceId->external_id,
								                                                "name" => $unit->product->name[0]->value,
							                                                ],
						                                                ],
					                                                ],
				                                                ]);
			}

			$representation->save();

			// We also want to validate that the property has a representation as well
			$propertyRepresentation = $product->property->external_representations()
			                                            ->firstWhere("inventory_id", "=", $this->inventoryId);

			if (!$propertyRepresentation) {
				// Add it
				$propertyRepresentation              = ExternalInventoryResource::fromInventoryResource($unit->product->property_id);
				$propertyRepresentation->resource_id = $product->property->inventory_resource_id;
				$propertyRepresentation->save();
			}

			// Set up inventory for auto push
			$inventorySettings               = $product->property->inventories_settings()
			                                                     ->where("inventory_id", "=", $this->inventoryId)
			                                                     ->firstOrCreate([
				                                                                     "resource_id" => $product->property->inventory_resource_id,
				                                                                                                                                                                                                                                                                                                                                                    "inventory_id" => $inventoryId,
			                                                                     ], [
				                                                                     "is_enabled"   => true,
				                                                                     "push_enabled" => true,
				                                                                     "pull_enabled" => false,
				                                                                     "settings"     => "{}",
			                                                                     ]);
			$inventorySettings->push_enabled = true;
			$inventorySettings->save();

			$this->output?->writeln(": Associated to " . $product->property->actor->name . " - " . $product->name_en);
		}

		$spreadsheet = new Spreadsheet();
		$worksheet   = new Worksheet(null, 'Worksheet 1');
		$spreadsheet->addSheet($worksheet);
		$spreadsheet->removeSheetByIndex(0);

		foreach ($notMatched as $unitName) {
			$this->output?->writeln($unitName);
			$worksheet->printRow([$unitName]);
		}

		$filename = storage_path("hivestack-missing.xlsx");
		$writer   = new Xlsx($spreadsheet);
		$writer->save($filename);
		$this->output?->writeln($filename);

		return true;
	}
}
