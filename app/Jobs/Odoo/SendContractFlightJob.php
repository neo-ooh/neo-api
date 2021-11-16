<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractFlightJob.php
 */

namespace Neo\Jobs\Odoo;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Odoo\ProductCategory;
use Neo\Models\Odoo\ProductType;
use Neo\Models\Property;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\Models\Product;
use Neo\Services\Odoo\OdooConfig;

class SendContractFlightJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 3600;

    public function __construct(protected Contract $contract, protected array $flight, protected int $flightIndex) {
    }

    public function handle() {
        clock()->event("Send Flight")->begin();

        $client = OdooConfig::fromConfig()->getClient();

        $flightType  = $this->flight["type"];
        $flightStart = Carbon::parse($this->flight['start'])->toDateString();
        $flightEnd   = Carbon::parse($this->flight['end'])->toDateString();

        clock()->event("Create flight in Odoo")->color('purple')->begin();

        $campaign = clock(Campaign::create($client, [
            "order_id"   => $this->contract->id,
            "state"      => "draft",
            "date_start" => $flightStart,
            "date_end"   => $flightEnd,
        ]));

        clock()->event("Create flight in Odoo")->end();

        // Preload the properties and products used by the flight
        $properties         = Property::with("odoo")->findMany(collect($this->flight["selection"])->pluck("0")->unique());
        $productsCategories = ProductCategory::findMany(collect($this->flight["selection"])->pluck("1")->unique());

        // Now we need to add each specified product
        foreach ($this->flight["selection"] as $selection) {
            [$propertyId, $productId, $discount, $spotsCount] = $selection;

            $spotsCount = $spotsCount ?? 1;

            clock()->event("Handle product #$propertyId->$productId")->begin();

            // We need the property and product record from Connect
            /** @var Property $connectProperty */
            $connectProperty = $properties->firstOrFail(fn($property) => $property->getKey() === $propertyId);
            /** @var ProductType $connectProductType */
            $connectProductType = $productsCategories->firstOrFail(fn($product) => $product->getKey() === $productId);

            // Pull the products of the odoo property matching the product type
            $products = Product::all($client, [
                ["shopping_center_id", "=", $connectProperty->odoo->odoo_id],
                ["categ_id", "=", $connectProductType->odoo_id]
            ]);

            // Filter products based on flight type
            if ($flightType === 'bua') {
                $products = $products->where("bonus", "=", true);
            } else {
                $products = $products->where("bonus", "=", false);
            }

            // As of 2021-10-07, behaviour of selection of Mall posters is not set. Current desired behaviour is to support adding only one poster per property.

            $productIterator = $products->getIterator();

            // If no product is available, skip it
            if (!$productIterator->valid()) {
                continue;
            }

            // We add products until we have one that is available, or we add all if its digital
            do {
                $product    = $productIterator->current();
                $linkedLine = null;

                $orderLine = OrderLine::create($client, [
                    "order_id"        => $this->contract->id,
                    "name"            => $product->name,
                    "price_unit"      => $product->list_price,
                    "product_uom_qty" => $spotsCount,
                    "customer_lead"   => 0.0,
                    "product_id"      => $product->product_variant_id[0],
                    "rental_start"    => $flightStart,
                    "rental_end"      => $flightEnd,
                    "is_rental_line"  => 1,
                    "discount"        => $flightType === 'bonus' ? 100.0 : $discount,
                    "sequence"        => $this->flightIndex * 10,
                ]);

                // If the product has a linked product, we add it as well
                if ($product->linked_product_id) {
                    // Get the linked product
                    $linkedProduct = Product::get($client, $product->linked_product_id[0]);

                    $linkedLine = OrderLine::create($client, [
                        "order_id"        => $this->contract->id,
                        "name"            => $linkedProduct->name,
                        "price_unit"      => 0,
                        "product_uom_qty" => 1.0,
                        "customer_lead"   => 0.0,
                        "product_id"      => $linkedProduct->product_variant_id[0],
                        "rental_start"    => $flightStart,
                        "rental_end"      => $flightEnd,
                        "is_rental_line"  => 1,
                        "is_linked_line"  => 1,
                        "discount"        => 0,
                        "sequence"        => $this->flightIndex * 10
                    ]);
                }

                $productIterator->next();

                // If the product is unavailable, and we have other products that we can try with, remove the product.
                if ($orderLine->over_qty > 0 && $productIterator->valid()) {
                    $orderLine->remove();
                    $linkedLine?->remove();
                }
            } while (($orderLine->over_qty > 0 || $connectProductType->product_type_id === 2) && $productIterator->valid());

            clock()->event("Handle product #$propertyId->$productId")->end();
        }
        clock()->event("Send Flight")->end();
    }
}
