<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractJob.php
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

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected array $flights, protected bool $clearOnSend) {
    }

    public function handle() {
        clock()->event('Send contract')->color('purple')->begin();

        $client = OdooConfig::fromConfig()->getClient();

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required orderlines
        foreach ($this->flights as $flightKey => $flight) {
            clock()->event("Send Flight #".$flightKey)->begin();

            if (!$flight['send']) {
                clock()->info("Flight #$flightKey is not marked for sending");
                clock()->event("Send Flight #".$flightKey)->end();
                continue;
            }

            $flightType  = $flight["type"];
            $flightStart = Carbon::parse($flight['start'])->toDateString();
            $flightEnd   = Carbon::parse($flight['end'])->toDateString();

            clock()->event("Create flight in Odoo")->begin();

            $campaign = clock(Campaign::create($client, [
                "order_id"   => $this->contract->id,
                "state"      => "draft",
                "date_start" => $flightStart,
                "date_end"   => $flightEnd,
            ]));

            clock()->event("Create flight in Odoo")->end();

            // Preload the properties and products used by the flight
            $properties = Property::with("odoo")->findMany(collect($flight["selection"])->pluck("0.0"));
            $products = ProductCategory::findMany(collect($flight["selection"])->pluck("0.1"));

            // Now we need to add each specified product
            foreach ($flight["selection"] as $selection) {
                clock()->event("Send product #". implode(",", $selection[0]))->start();

                $propertyId = $selection[0][0];
                $productId  = $selection[0][1];

                // We need the property and product record from Connect
                /** @var Property $connectProperty */
                $connectProperty = $properties->first(fn($property) => $property->getKey()  ===  $propertyId);
                /** @var ProductType $connectProduct */
                $connectProduct = $products->first(fn($product) => $product->getKey()  ===  $productId);

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

                /** @var Product $product */
                foreach ($products as $product) {     // Add a new order line with the first product
                    OrderLine::create($client, [
                        "order_id"        => $this->contract->id,
                        "name"            => $product->name,
                        "price_unit"      => $product->list_price,
                        "product_uom_qty" => 1.0,
                        "customer_lead"   => 0.0,
                        "product_id"      => $product->product_variant_id[0],
                        "rental_start"    => $flightStart,
                        "rental_end"      => $flightEnd,
                        "is_rental_line"  => 1,
                        "discount"        => $flightType === 'bonus' ? 100.0 : 0.0
                    ]);
                }

                clock()->event("Send product #". implode(",", $selection[0]))->start();
            }
            clock()->event("Send Flight #".$flightKey)->end();
        }

        clock()->event('Send contract')->end();
    }
}
