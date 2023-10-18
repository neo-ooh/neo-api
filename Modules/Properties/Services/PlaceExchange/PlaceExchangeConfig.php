<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlaceExchangeConfig.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange;

use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\InventoryType;

class PlaceExchangeConfig extends InventoryConfig {
	public InventoryType $type = InventoryType::PlaceExchange;

	public function __construct(
		public string $name,
		public int    $inventoryID,
		public string $inventoryUUID,
		public string $api_url,
		public string $api_username,
		public string $api_key,
		public string $org_id,
		public string $conversion_rate_usd_to_cad,
	) {
	}

	public function getClient() {
		return new PlaceExchangeClient($this);
	}
}
