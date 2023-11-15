<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractLineResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Properties\Services\Resources\Enums\ContractLineType;

class ContractLineResource extends InventoryResource {
	public function __construct(
		/**
		 * @var InventoryResourceId|null ID of the line in the external inventory
		 */
		public InventoryResourceId|null $line_id,

		/**
		 * @var InventoryResourceId ID of the product the line is attached to in the external inventory
		 */
		public InventoryResourceId      $product_id,

		/**
		 * @var int Order of the line in the contract
		 */
		public int                      $order,

		/**
		 * @var string Name of the line
		 */
		public string                   $name,

		/**
		 * @var string Start date of the sale for this line
		 */
		public string                   $start_date,

		/**
		 * @var string End date of the sale for this line
		 */
		public string                   $end_date,

		/**
		 * @var ContractLineType The line type
		 */
		public ContractLineType         $type,

		/**
		 * @var bool Tell if the line is linked. A linked line is used to represent availabilities being taken
		 */
		public bool                     $is_linked,

		/**
		 * @var int Number of faces of the product
		 */
		public int                      $faces_count,

		/**
		 * @var float The number of spots sold, only applies to digital products
		 */
		public float                    $spots_count,

		/**
		 * @var float Traffic for the line, only applied to OOH lines
		 */
		public float                    $traffic,

		/**
		 * @var float Impressions for the line
		 */
		public float                    $impressions,

		/**
		 * @var float The product unit price: Its price per face per week
		 */
		public float                    $unit_price,

		/**
		 * @var float The media value
		 */
		public float                    $media_value,

		/**
		 * @var float Discount percentage
		 */
		public float                    $discount_amount_relative,

		/**
		 * @var float Discount value
		 */
		public float                    $discount_amount,

		/**
		 * @var float Actual price for this line, or net investement
		 */
		public float                    $price,

		/**
		 * @var float CPM for the current product
		 */
		public float                    $cpm,

		/**
		 * @var string Description, used by mobile products
		 */
		public string                   $description = "",

		/**
		 * @var string Campaign targeting, used by mobile products
		 */
		public string                   $targeting = "",

		/**
		 * @var string Type of mobile product
		 */
		public string                   $mobile_type = "",
	) {
	}
}
