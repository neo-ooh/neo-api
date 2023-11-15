<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OrderLine.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

use Neo\Modules\Properties\Services\Resources\ContractLineResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractLineType;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

/**
 * @property int    $id
 * @property array  $order_id
 * @property string $name
 * @property number $sequence
 * @property array  $invoice_lines
 * @property int    $price_unit
 * @property int    $price_subtotal
 * @property int    $price_tax
 * @property int    $price_total
 * @property int    $price_reduce
 * @property array  $product_id
 * @property array  $product_template_id
 * @property int    $over_qty
 * @property int    $product_uom_qty
 * @property int    $customer_lead
 * @property string $rental_start
 * @property string $rental_end
 * @property int    $is_rental_line
 * @property int    $is_linked_line
 * @property int    $discount
 * @property int    $nb_weeks
 * @property int    $nb_screen
 * @property int    $connect_impression
 * @property int    $impression
 *
 * @property string $market_name
 * @property string $segment
 * @property string $impression_format
 * @property string $cpm
 * ...
 */
class OrderLine extends OdooModel {
	public static string $slug = "sale.order.line";

	protected static array $filters = [];

	public function toResource(int $inventoryId) {
		$type = ContractLineType::Guaranteed;
		if (str_starts_with($this->name, "[Production]")) {
			$type = ContractLineType::ProductionCost;
		} else if ($this->name === "Audience extension strategy") {
			$type = ContractLineType::Mobile;
		} else if ($this->discount > 99.9) {
			$type = ContractLineType::Bonus;
		} else if ($this->price_subtotal < PHP_FLOAT_EPSILON && $this->discount === 0) {
			$type = ContractLineType::BUA;
		}

		return new ContractLineResource(
			line_id                 : new InventoryResourceId(
				                          inventory_id: $inventoryId,
				                          external_id : $this->getKey(),
				                          type        : InventoryResourceType::ContractLine
			                          ),
			product_id              : new InventoryResourceId(
				                          inventory_id: $inventoryId,
				                          external_id : $this->product_id[0],
				                          type        : InventoryResourceType::Product
			                          ),
			order                   : $this->sequence,
			name                    : $this->name,
			start_date              : $this->rental_start,
			end_date                : $this->rental_end,
			type                    : $type,
			is_linked               : $this->is_linked_line,
			faces_count             : $this->nb_screen,
			spots_count             : $this->product_uom_qty,
			traffic                 : 0,
			impressions             : $this->connect_impression ?: $this->impression,
			unit_price              : $this->price_unit,
			media_value             : $this->price_unit * $this->nb_weeks * $this->nb_screen * $this->product_uom_qty,
			discount_amount_relative: $this->discount,
			discount_amount         : ($this->price_unit * $this->nb_weeks) * ($this->discount / 100),
			price                   : $this->price_subtotal,
			cpm                     : $this->cpm,
			description             : $this->market_name,
			targeting               : $this->segment,
			mobile_type             : $this->impression_format,
		);
	}
}
