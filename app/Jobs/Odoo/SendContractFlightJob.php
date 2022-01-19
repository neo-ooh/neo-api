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
use Neo\Models\Product;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;

class SendContractFlightJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    /**
     * @var Collection List of all Connect's products included in this flight
     */
    protected Collection $products;

    /**
     * @var Collection List of all Connect's properties included in this flight
     */
    protected Collection $properties;

    protected string $flightType;

    protected Carbon $flightStartDate;
    protected string $flightStart;
    protected Carbon $flightEndDate;
    protected string $flightEnd;

    /**
     * @var \Illuminate\Support\Collection Keeps track of all the products sent to Odoo for this flight.
     */
    protected \Illuminate\Support\Collection $consumedProducts;

    public function __construct(protected Contract $contract, protected array $flight, protected int $flightIndex) {
    }

    public function handle() {
        $client = OdooConfig::fromConfig()->getClient();

        // We need to extract all the products included in this flight.
        // This way, we only make one request to the db for the correct Odoo ids
        $compiledProducts = collect($this->flight["properties"])->flatMap(fn($property) => collect($property["categories"])->flatMap(fn($category) => $category["products"]));
        /** @var Collection<Product> $products */
        $this->products = Product::query()->whereIn("id", $compiledProducts->pluck("id"))->get();

        // Load linked products id as well
        $linkedProductsIds = $this->products->pluck("external_linked_id")->filter();
        $this->products    = $this->products->merge(Product::query()
                                                           ->whereIn("external_id", $linkedProductsIds)
                                                           ->get())->unique();

        // Register the flight campaign in the contract
        Campaign::create($client, [
            "order_id"   => $this->contract->id,
            "state"      => "draft",
            "date_start" => $this->flight["start"],
            "date_end"   => $this->flight["end"],
        ], pullRecord: false);

        // Now, we loop over each compiled product, and build its orderlines
        $orderLinesToAdd = collect();

        foreach ($compiledProducts as $compiledProduct) {
            $orderLinesToAdd->push(...$this->buildLines($this->products->firstWhere("id", "=", $compiledProduct["id"]), $compiledProduct));
        }

        $client->client->call(OrderLine::$slug, "create", [$orderLinesToAdd->toArray()]);

        // And we are done
    }

    protected function buildLines(Product $product, array $compiledProduct) {
        $orderLines = collect();

        $orderLines->push([
            "order_id"           => $this->contract->id,
            "name"               => $product->name,
            "price_unit"         => $product->unit_price,
            "product_uom_qty"    => $compiledProduct["spots"],
            "customer_lead"      => 0.0,
            "nb_screen"          => $compiledProduct["quantity"],
            "product_id"         => $product->external_variant_id,
            "rental_start"       => $this->flight["start"],
            "rental_end"         => $this->flight["end"],
            "is_rental_line"     => 1,
            "is_linked_line"     => 0,
            "discount"           => (1 - ($compiledProduct["price"] / $compiledProduct["media_value"])) * 100,
            "sequence"           => $this->flightIndex * 10,
            "connect_impression" => $compiledProduct["impressions"],
        ]);

        if (!$product->external_linked_id) {
            return $orderLines;
        }

        /** @var Product|null $product */
        $linkedProduct = $this->products->firstWhere("external_id", "=", $product->external_linked_id);

        if (!$linkedProduct) {
            return $orderLines;
        }

        $orderLines->push([
            "order_id"        => $this->contract->id,
            "name"            => $linkedProduct->name,
            "price_unit"      => 0,
            "product_uom_qty" => $compiledProduct["spots"],
            "customer_lead"   => 0.0,
            "nb_screen"       => $linkedProduct->quantity,
            "product_id"      => $linkedProduct->external_variant_id,
            "rental_start"    => $this->flight["start"],
            "rental_end"      => $this->flight["end"],
            "is_rental_line"  => 1,
            "is_linked_line"  => 1,
            "discount"        => 0,
            "sequence"        => $this->flightIndex * 10
        ]);

        return $orderLines;
    }

//    public function handle() {
//        $this->consumedProducts = collect();
//        $client                 = OdooConfig::fromConfig()->getClient();
//
//        $this->flightType      = $this->flight["type"];
//        $this->flightStartDate = Carbon::parse($this->flight['start']);
//        $this->flightEndDate   = Carbon::parse($this->flight['end']);
//
//        $this->flightStart = $this->flightStartDate->toDateString();
//        $this->flightEnd   = $this->flightEndDate->toDateString();
//
//        Campaign::create($client, [
//            "order_id"   => $this->contract->id,
//            "state"      => "draft",
//            "date_start" => $this->flightStart,
//            "date_end"   => $this->flightEnd,
//        ], pullRecord: false);
//
//        // Load all the Connect's products included in the flight
//        $this->products = Product::query()
//                                 ->with(["impressions_models", "category", "category.impressions_models"])
//                                 ->whereInMultiple(['property_id', 'category_id'], $this->flight["selection"])
//                                 ->where("is_bonus", "=", $this->flightType === "bua")
//                                 ->get();
//
//        // Load all the properties with their traffic as well
//        $this->properties = Property::query()
//                                    ->with(["traffic", "traffic.weekly_data"])
//                                    ->whereIn("actor_id", collect($this->flight["selection"])->pluck(0))
//                                    ->get()
//                                    ->each(fn(Property $property) => $property->rolling_weekly_traffic = $property->traffic->getRollingWeeklyTraffic());
//
//        // Load linked products id as well
//        $linkedProductsIds = $this->products->pluck("external_linked_id")->filter();
//        $this->products    = $this->products->merge(Product::query()
//                                                           ->whereIn("external_id", $linkedProductsIds)
//                                                           ->get())->unique();
//
//        $orderLinesToAdd = collect();
//
//        foreach ($this->flight["selection"] as $selection) {
//            /**
//             * @var int $propertyId        ID of the property
//             * @var int $productCategoryId ID of the category of products to add
//             * @var int $discount          Discount to apply to all the products in the category, for this property
//             * @var int $spotsCount        Number of spots selected, influences impressions
//             */
//            [$propertyId, $productCategoryId, $discount, $spotsCount] = $selection;
//
//            /** @var Collection<Product> $product */
//            $products = $this->products->filter(fn($p) => $p->property_id === $propertyId && $p->category_id === $productCategoryId);
//
//            if ($products->isEmpty()) {
//                Log::info("Could not find product for selection pair: [$propertyId, $productCategoryId]");
//                continue;
//            }
//
//            // For `DIGITAL` categories, we add all products
//            // For `STATIC` categories, we add as many product as requested through the `$spotsCount` value.
//
//            $productsPointer = $products->getIterator();
//            $productsCount   = 0;
//
//            do {
//                $product = $productsPointer->current();
//                $orderLinesToAdd->push(...$this->buildLines(
//                    $product,
//                    spotsCount: $product->category->fill_strategy === ProductsFillStrategy::digital ? $spotsCount : 1,
//                    discount: $discount
//                ));
//
//                $productsPointer->next();
//                ++$productsCount;
//            } while (($product->category->fill_strategy === ProductsFillStrategy::digital || $productsCount < $spotsCount) && $productsPointer->valid());
//        }
//
//        // Now that we have all our order  lines, push them to the server
//        $addedOrderLines = $client->client->call(OrderLine::$slug, "create", [$orderLinesToAdd->toArray()]);
//
//        // We now want to load the order lines that we just added, check if they are some that are overbooked, and try to find a replacement for these ones
//
//        // Load all the order lines we just added
//        $orderLinesAdded = OrderLine::getMultiple($client, $addedOrderLines->toArray());
//        $overbookedLines = $orderLinesAdded->where("over_qty", ">", "0");
//
//        // Reset the list of order lines to add
//        $orderLinesToRemove = collect();
//
//        do {
//            $orderLinesToAdd = collect();
//
//            /** @var OrderLine $line */
//            // For each overbooked product, we try to find another for one for the same property, in the same category, that has not been already used (consumed).
//            foreach ($overbookedLines as $line) {
//                /** @var Product $lineProduct */
//                $lineProduct = $this->products->firstWhere("external_variant_id", "=", $line->product_id[0]);
//                $product     = $this->products->filter(fn($p) => $p->property_id === $lineProduct->property_id && $p->category_id === $lineProduct->category_id)
//                                              ->whereNotIn("external_id", $this->consumedProducts)
//                                              ->first();
//
//                if (!$product) {
//                    // No more products available, nothing left to do
//                    continue;
//                }
//
//                // Set up the new lines to try
//                $spotsCount = $line->product_uom_qty;
//                $discount   = $line->discount;
//
//                $orderLinesToAdd->push(...$this->buildLines($product, $spotsCount, $discount));
//
//                // Mark the previous lines as to be removed
//                $orderLinesToRemove->push($line->id);
//
//                if ($lineProduct->external_linked_id && $linkedProduct = $this->products->get($lineProduct->external_linked_id)) {
//                    $orderLinesToRemove->push($orderLinesAdded->first(/**
//                     * @param OrderLine $line
//                     * @return mixed
//                     */ fn($line) => $line->is_linked_line && $line->product_id[0] === $linkedProduct->external_variant_id)->id);
//                }
//            }
//
//            if ($orderLinesToAdd->count() === 0) {
//                // No line to add, we stop adding stuff here
//                break;
//            }
//
//            // Now that we have all our orderlines, push them to the server and load them for verification
//            $addedOrderLines = $client->client->call(OrderLine::$slug, 'create', [$orderLinesToAdd->toArray()]);
//            $orderLines      = OrderLine::getMultiple($client, $addedOrderLines->toArray());
//            $overbookedLines = $orderLines->where("over_qty", ">", "0");
//
//        } while ($overbookedLines->count() > 0);
//
//        if ($orderLinesToRemove->count() > 0) {
//            OrderLine::delete($client, [
//                ["id", "in", $orderLinesToRemove->toArray()]
//            ]);
//        }
//    }
//
//    protected function buildLines(Product $product, float $spotsCount, float $discount) {
//        $orderLines = collect();
//
//        $orderLines->push([
//            "order_id"           => $this->contract->id,
//            "name"               => $product->name,
//            "price_unit"         => $product->unit_price,
//            "product_uom_qty"    => $spotsCount,
//            "customer_lead"      => 0.0,
//            "product_id"         => $product->external_variant_id,
//            "rental_start"       => $this->flightStart,
//            "rental_end"         => $this->flightEnd,
//            "is_rental_line"     => 1,
//            "is_linked_line"     => 0,
//            "discount"           => $this->flightType === 'bonus' ? 100.0 : $discount,
//            "sequence"           => $this->flightIndex * 10,
//            "connect_impression" => $this->getProductImpressions($product, $spotsCount),
//        ]);
//
//        $this->consumedProducts->push($product->external_id);
//
//        if (!$product->external_linked_id) {
//            return $orderLines;
//        }
//
//        /** @var Product|null $product */
//        $linkedProduct = $this->products->firstWhere("external_id", "=", $product->external_linked_id);
//
//        if (!$linkedProduct) {
//            return $orderLines;
//        }
//
//        $orderLines->push([
//            "order_id"        => $this->contract->id,
//            "name"            => $linkedProduct->name,
//            "price_unit"      => 0,
//            "product_uom_qty" => 1.0,
//            "customer_lead"   => 0.0,
//            "product_id"      => $linkedProduct->external_variant_id,
//            "rental_start"    => $this->flightStart,
//            "rental_end"      => $this->flightEnd,
//            "is_rental_line"  => 1,
//            "is_linked_line"  => 1,
//            "discount"        => 0,
//            "sequence"        => $this->flightIndex * 10
//        ]);
//
//        return $orderLines;
//    }
}
