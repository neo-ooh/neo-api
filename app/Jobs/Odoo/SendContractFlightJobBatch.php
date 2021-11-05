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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Odoo\Product;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;

class SendContractFlightJobBatch implements ShouldQueue {
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

        clock(Campaign::create($client, [
            "order_id"   => $this->contract->id,
            "state"      => "draft",
            "date_start" => $flightStart,
            "date_end"   => $flightEnd,
        ], pullRecord: false));

        clock()->event("Create flight in Odoo")->end();

        clock()->event("Prepare order lines")->begin();
        // Load all the Connect's products included in the flight
        /** @var Collection $products */
        $products = Product::query()
                           ->whereInMultiple(['property_id', 'product_category_id'], $this->flight["selection"])
                           ->where("is_bonus", "=", $flightType === "bua")
                           ->get();

        $linkedProductsIds = $products->pluck("linked_product_id")->filter();
        $products = $products->merge(Product::query()
                                ->whereIn("odoo_id", $linkedProductsIds)
                                ->get());

        $orderLines = collect();

        foreach ($this->flight["selection"] as $selection) {
            [$propertyId, $productId, $discount, $spotsCount] = $selection;

            /** @var Product|null $product */
            $product = $products->first(fn($p) => $p->property_id === $propertyId && $p->product_category_id === $productId);

            if (!$product) {
                clock("Could not find product for selection pair: [$propertyId, $productId]");
                continue;
            }

            $orderLines->push([
                "order_id"        => $this->contract->id,
                "name"            => $product->name,
                "price_unit"      => $product->unit_price,
                "product_uom_qty" => $spotsCount,
                "customer_lead"   => 0.0,
                "product_id"      => $product->odoo_variant_id,
                "rental_start"    => $flightStart,
                "rental_end"      => $flightEnd,
                "is_rental_line"  => 1,
                "is_linked_line"  => 0,
                "discount"        => $flightType === 'bonus' ? 100.0 : $discount,
                "sequence"        => $this->flightIndex * 10,
            ]);

            if ($product->linked_product_id) {
                /** @var Product|null $product */
                $linkedProduct = $products->get($product->linked_product_id);

                if (!$linkedProduct) {
                    continue;
                }

                $orderLines->push([
                    "order_id"        => $this->contract->id,
                    "name"            => $linkedProduct->name,
                    "price_unit"      => 0,
                    "product_uom_qty" => 1.0,
                    "customer_lead"   => 0.0,
                    "product_id"      => $linkedProduct->odoo_variant_id,
                    "rental_start"    => $flightStart,
                    "rental_end"      => $flightEnd,
                    "is_rental_line"  => 1,
                    "is_linked_line"  => 1,
                    "discount"        => 0,
                    "sequence"        => $this->flightIndex * 10
                ]);
            }
        }

        clock()->event("Prepare order lines")->end();

        // Now that we have all our orderlines, push them to the server
        $client->create(OrderLine::$slug, $orderLines->toArray());

        clock()->event("Send Flight")->end();
    }
}
