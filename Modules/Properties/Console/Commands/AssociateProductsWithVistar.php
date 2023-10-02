<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AssociateProductsWithVistar.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Support\Facades\App;
use Neo\Documents\XLSX\Worksheet;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\InventoryType;
use Neo\Modules\Properties\Services\Reach\ReachAdapter;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Output\ConsoleOutput;

class AssociateProductsWithVistar extends Job {
	public ConsoleOutput|null $output = null;

	public function __construct(public int $inventoryId) {
		if (App::runningInConsole()) {
			$this->output = new ConsoleOutput();
		}
	}

	protected function run(): mixed {
		$provider = InventoryProvider::query()->findOrFail($this->inventoryId);
		/** @var ReachAdapter $inventory */
		$inventory = $provider->getAdapter();

		if ($inventory->getInventoryType() !== InventoryType::Vistar) {
			$this->output?->writeln("Bad Inventory ID");
			return false;
		}

		$venues = $inventory->listProducts();

		$notMatched = [];

		/** @var IdentifiableProduct $venue */
		foreach ($venues as $venue) {

			// Ignore disabled products
			if (!$venue->product->is_sellable) {
				continue;
			}

			$this->output?->write("#" . $venue->resourceId->external_id . " " . $venue->product->name[0]->value);

			// Find the location this venue is representing
			$playerExternalId = $venue->resourceId->context["player_external_id"];

			$player = Player::query()->where("external_id", "=", $playerExternalId)->first();

			if (!$player) {
				$this->output?->writeln(": No player found.");
				$notMatched[] = $venue->resourceId->external_id . ":" . $venue->product->name[0]->value;
				continue;
			}

			$location = $player->location;

			$location->load("products.property");

			$products = $location->products->where("is_bonus", "=", false);
			if ($products->count() === 0) {
				$this->output?->writeln(": No products found for venue.");
				$notMatched[] = $venue->resourceId->external_id . ":" . $venue->product->name[0]->value;
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
				if (is_array($representation->context->venues)) {
					$representation->context->venues[$player->getKey()] = [
						"id"   => $venue->resourceId->external_id,
						"name" => $venue->product->name[0]->value,
					];
				} else {
					$representation->context->venues = [$player->getKey() => [
						"id"   => $venue->resourceId->external_id,
						"name" => $venue->product->name[0]->value,
					]];
				}
			} else {
				$representation = new ExternalInventoryResource([
					                                                "resource_id"  => $product->inventory_resource_id,
					                                                "inventory_id" => $this->inventoryId,
					                                                "type"         => InventoryResourceType::Product,
					                                                "external_id"  => "MULTIPLE",
					                                                "context"      => [
						                                                "network_id" => $venue->resourceId->context["network_id"],
						                                                "venues"     => [
							                                                $player->getKey() => [
								                                                "id"   => $venue->resourceId->external_id,
								                                                "name" => $venue->product->name[0]->value,
							                                                ],
						                                                ],
					                                                ],
				                                                ]);
			}

			$representation->save();

			$inventorySettings               = $product->property->inventories_settings()
			                                                     ->where("inventory_id", "=", $this->inventoryId)
			                                                     ->firstOrCreate([
				                                                                     "resource_id" => $product->property->inventory_resource_id,
				                                                                                                                                                                                                                                                                                                                                           "inventory_id" => $this->inventoryId,
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

		$filename = storage_path("vistar-missing.xlsx");
		$writer   = new Xlsx($spreadsheet);
		$writer->save($filename);
		$this->output?->writeln($filename);

		return true;
	}
}
