<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AssociateInventoryProductsCommand.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryType;

class AssociateInventoryProductsCommand extends Command {
	protected $signature = 'inventories:associate {inventoryId}';

	protected $description = 'Command description';

	public function handle(): void {
		$inventoryId = $this->argument("inventoryId");
		/** @var InventoryProvider|null $inventory */
		$inventory = InventoryProvider::query()->find($inventoryId);

		if (!$inventory) {
			$this->error("Could not find inventory with ID $inventoryId.");
		}

		switch ($inventory->provider) {
			case InventoryType::Odoo:
				$this->error("Odoo inventories does not support auto-matching of products");
				break;
			case InventoryType::Hivestack:
				(new AssociateProductsWithHivestack($inventoryId))->handle();
				break;
			case InventoryType::Vistar:
				(new AssociateProductsWithVistar($inventoryId))->handle();
				break;
			case InventoryType::Reach:
				(new AssociateProductsWithReach($inventoryId))->handle();
				break;
			case InventoryType::PlaceExchange:
				$this->error("PlaceExchange inventories does not support auto-matching of products");
				break;
			case InventoryType::Dummy:
				$this->error("Dummy inventories does not support auto-matching of products");

		}
	}
}
