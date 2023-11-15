<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdvertiserResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

class AdvertiserResource extends InventoryResource {
	public function __construct(
		/**
		 * Id of the inventory system the advertiser is part of
		 */
		public int    $inventory_id,

		/**
		 * @var string Actual ID of the advertiser in the inventory system
		 */
		public string $external_id,

		/**
		 * The advertiser name
		 */
		public string $name,
	) {
	}
}
