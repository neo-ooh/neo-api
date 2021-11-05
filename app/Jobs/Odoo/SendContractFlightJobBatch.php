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
    public $timeout = 30;

    protected Collection $products;

    protected string $flightType;
    protected string $flightStart;
    protected string $flightEnd;

    protected \Illuminate\Support\Collection $consumedProducts;

    public function __construct(protected Contract $contract, protected array $flight, protected int $flightIndex) {}

    public function handle() {
        clock()->event("Send Flight")->begin();

        $this->consumedProducts = collect();
        $client = OdooConfig::fromConfig()->getClient();

        $this->flightType  = $this->flight["type"];
        $this->flightStart = Carbon::parse($this->flight['start'])->toDateString();
        $this->flightEnd   = Carbon::parse($this->flight['end'])->toDateString();

        clock()->event("Create flight in Odoo")->color('purple')->begin();

        clock(Campaign::create($client, [
            "order_id"   => $this->contract->id,
            "state"      => "draft",
            "date_start" => $this->flightStart,
            "date_end"   => $this->flightEnd,
        ], pullRecord: false));

        clock()->event("Create flight in Odoo")->end();

        clock()->event("Prepare order lines")->begin();
        // Load all the Connect's products included in the flight
        $this->products = Product::query()
                           ->whereInMultiple(['property_id', 'product_category_id'], $this->flight["selection"])
                           ->where("is_bonus", "=", $this->flightType === "bua")
                           ->get();

        $linkedProductsIds = $this->products->pluck("linked_product_id")->filter();
        $this->products = $this->products->merge(Product::query()
                                ->whereIn("odoo_id", $linkedProductsIds)
                                ->get())->unique();

        $orderLinesToAdd = collect();

        foreach ($this->flight["selection"] as $selection) {
            [$propertyId, $productId, $discount, $spotsCount] = $selection;

            /** @var Product|null $product */
            $product = $this->products->first(fn($p) => $p->property_id === $propertyId && $p->product_category_id === $productId);

            if (!$product) {
                clock("Could not find product for selection pair: [$propertyId, $productId]");
                continue;
            }

            $orderLinesToAdd->push(...$this->buildLines($product, spotsCount: $spotsCount, discount: $discount));
        }

        clock()->event("Prepare order lines")->end();

        // Now that we have all our orderlines, push them to the server
        $addedOrderLines = clock($client->client->call(OrderLine::$slug, "create", [$orderLinesToAdd->toArray()]));

        // We now want to load the order lines that we just added, check if they are some that are overbooked, and try to find a replacement for these ones

        // Load all the orderlines we just added
        $orderLinesAdded = OrderLine::getMultiple($client, $addedOrderLines->toArray());
        $overbookedLines = $orderLinesAdded->where("over_qty", ">", "0");

        // Reset the list of orderlines to add
        $orderLinesToAdd = collect();
        $orderLinesToRemove = collect();

        do {
            /** @var OrderLine $line */
            clock($this->consumedProducts);
            foreach ($overbookedLines as $line) {
                /** @var Product $lineProduct */
                $lineProduct = $this->products->firstWhere("odoo_variant_id", "=", $line->product_id[0]);
                $product     = $this->products->filter(fn($p) =>
                    $p->property_id === $lineProduct->property_id && $p->product_category_id === $lineProduct->product_category_id)
                                          ->whereNotIn("odoo_id", $this->consumedProducts)
                                          ->first();

                if(!$product) {
                    // No more products available, nothing left to do
                    continue;
                }

                // Set up the new lines to try
                $spotsCount = $line->product_uom_qty;
                $discount = $line->discount;

                $orderLinesToAdd->push(...$this->buildLines($product, $spotsCount, $discount));

                // Mark the previous lines as to be removed
                $orderLinesToRemove->push($line->id);

                if($lineProduct->linked_product_id && $linkedProduct = $this->products->get($lineProduct->linked_product_id)) {
                    $orderLinesToRemove->push($orderLinesAdded->first(/**
                     * @param OrderLine $line
                     * @return mixed
                     */ fn($line) => $line->is_linked_line && $line->product_id[0] === $linkedProduct->odoo_variant_id)->id);
                }
            }

            if($orderLinesToAdd->count() === 0) {
                // No line to add, we stop adding stuff here
                break;
            }

            // Now that we have all our orderlines, push them to the server and load them for verification
            $addedOrderLines = $client->client->call(OrderLine::$slug, 'create', [$orderLinesToAdd->toArray()]);
            $orderLines = OrderLine::getMultiple($client, $addedOrderLines->toArray());
            $overbookedLines = $orderLines->where("over_qty", ">", "0");
        } while($overbookedLines->count() > 0);

        clock($orderLinesToRemove);

        if($orderLinesToRemove->count() > 0) {
            OrderLine::delete($client, [
                ["id", "in", $orderLinesToRemove->toArray()]
            ]);
        }

        clock()->event("Send Flight")->end();
    }

    protected function buildLines(Product $product, float $spotsCount, float $discount) {
        $orderLines = collect();

        $orderLines->push([
            "order_id"        => $this->contract->id,
            "name"            => $product->name,
            "price_unit"      => $product->unit_price,
            "product_uom_qty" => $spotsCount,
            "customer_lead"   => 0.0,
            "product_id"      => $product->odoo_variant_id,
            "rental_start"    => $this->flightStart,
            "rental_end"      => $this->flightEnd,
            "is_rental_line"  => 1,
            "is_linked_line"  => 0,
            "discount"        => $this->flightType === 'bonus' ? 100.0 : $discount,
            "sequence"        => $this->flightIndex * 10,
        ]);

        $this->consumedProducts->push($product->odoo_id);

        if (!$product->linked_product_id) {
            return $orderLines;
        }

        /** @var Product|null $product */
        $linkedProduct = $this->products->get($product->linked_product_id);

        if (!$linkedProduct) {
            return $orderLines;
        }

        $orderLines->push([
            "order_id"        => $this->contract->id,
            "name"            => $linkedProduct->name,
            "price_unit"      => 0,
            "product_uom_qty" => 1.0,
            "customer_lead"   => 0.0,
            "product_id"      => $linkedProduct->odoo_variant_id,
            "rental_start"    => $this->flightStart,
            "rental_end"      => $this->flightEnd,
            "is_rental_line"  => 1,
            "is_linked_line"  => 1,
            "discount"        => 0,
            "sequence"        => $this->flightIndex * 10
        ]);

        return $orderLines;
    }
}
