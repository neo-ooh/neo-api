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
        $properties         = Property::with("odoo")->findMany(collect($this->flight["selection"])->pluck("0.0")->unique());
        $productsCategories = ProductCategory::findMany(collect($this->flight["selection"])->pluck("0.1")->unique());

        // Now we need to add each specified product
        foreach ($this->flight["selection"] as $selection) {
            $key = implode(",", $selection[0]);
            clock()->event("Handle product #$key")->begin();

            [$propertyId, $productId] = $selection[0];

            // We need the property and product record from Connect
            /** @var Property $connectProperty */
            $connectProperty = $properties->firstOrFail(fn($property) => $property->getKey() === $propertyId);
            /** @var ProductType $connectProduct */
            $connectProduct = $productsCategories->firstOrFail(fn($product) => $product->getKey() === $productId);

            // Pull the products of the odoo property matching the product type
            $products = Product::all($client, [
                ["shopping_center_id", "=", $connectProperty->odoo->odoo_id],
                ["categ_id", "=", $connectProduct->odoo_id]
            ]);

            // Filter products based on flight type
            if ($flightType === 'bua') {
                $products = $products->where("bonus", "=", true);
            } else {
                $products = $products->where("bonus", "=", false);
            }

            // As of 2021-10-07, behaviour of selection of Mall posters is not set. Current desired behaviour is to support adding only one poster per property.

            /** @var Product $product */
            $productIterator = $products->getIterator();

            // If no product is available, skip it
            if(!$productIterator->valid()) {
                continue;
            }

            do {
                $product = $productIterator->current();

                $orderLine = OrderLine::create($client, [
                    "order_id"        => $this->contract->id,
                    "name"            => $product->name,
                    "price_unit"      => $product->list_price,
                    "product_uom_qty" => 1.0,
                    "customer_lead"   => 0.0,
                    "product_id"      => $product->product_variant_id[0],
                    "rental_start"    => $flightStart,
                    "rental_end"      => $flightEnd,
                    "is_rental_line"  => 1,
                    "discount"        => $flightType === 'bonus' ? 100.0 : 0.0,
                    "sequence"        => $this->flightIndex * 10,
                ]);

                if($orderLine->over_qty > 0) {
                    $orderLine->remove();
                }

                $productIterator->next();

            } while($orderLine->over_qty > 0 && $productIterator->valid());

            clock()->event("Handle product #$key")->end();
        }
        clock()->event("Send Flight")->end();
    }
}
