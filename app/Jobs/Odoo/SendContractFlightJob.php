<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractFlightJob.php
 */

namespace Neo\Jobs\Odoo;

use Carbon\Carbon;
use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\Models\Campaign;
use Neo\Modules\Properties\Services\Odoo\Models\Contract;
use Neo\Modules\Properties\Services\Odoo\Models\OrderLine;
use Neo\Modules\Properties\Services\Odoo\Models\Product as OdooProduct;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Resources\Contracts\CPCompiledCategory;
use Neo\Resources\Contracts\CPCompiledFlight;
use Neo\Resources\Contracts\CPCompiledProduct;
use Neo\Resources\Contracts\CPCompiledProperty;
use Spatie\LaravelData\Optional;

class SendContractFlightJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    readonly private int $odooInventoryId;

    public int $tries = 1;
    public int $timeout = 300;

    /**
     * @var Collection List of all Connect's products included in this flight
     */
    protected Collection $products;

    /**
     * @var Collection List of all Connect's properties included in this flight
     */
    protected Collection $properties;

    public function __construct(protected Contract $contract, protected CPCompiledFlight $flight, protected int $flightIndex) {
        $this->odooInventoryId = 1;
    }

    /**
     * @throws OdooException
     * @throws JsonException
     */
    public function handle(): array {
        $messages = [];

        $inventory = InventoryProvider::find($this->odooInventoryId);
        /** @var OdooAdapter $odoo */
        $odoo   = InventoryAdapterFactory::make($inventory);
        $client = $odoo->getConfig()->getClient();

        // We need to extract all the products included in this flight.
        // This way, we only make one request to the db for the correct Odoo ids
        $compiledProducts = $this->flight
            ->properties->toCollection()
                        ->flatMap(
                            fn(CPCompiledProperty $property) => $property
                                ->categories->toCollection()
                                            ->flatMap(
                                                fn(CPCompiledCategory $category) => $category->products->toCollection()
                                            )
                        );

        $this->products         = new Collection();
        $compiledProductsChunks = $compiledProducts->chunk(500);

        // Eloquent `whereIn` fails silently for references above ~1000 reference values
        foreach ($compiledProductsChunks as $chunk) {
            $this->products = $this->products->merge(Product::query()
                                                            ->whereIn("id", $chunk->pluck("id")->toArray())
                                                            ->with("external_representations")
                                                            ->get());
        }

        // Load linked products id as well
        $linkedProductsIds       = $this->products->pluck("linked_product_id")->filter()->unique();
        $loadedProductsIds       = $this->products->pluck("id");
        $linkedProductsIds       = $linkedProductsIds->whereNotIn(null, $loadedProductsIds);
        $linkedProductsIdsChunks = $linkedProductsIds->chunk(500);

        foreach ($linkedProductsIdsChunks as $chunk) {
            $this->products = $this->products->merge(Product::query()
                                                            ->whereIn("id", $chunk)
                                                            ->get());
        }

        // Register the flight campaign in the contract
        Campaign::create($client, [
            "order_id"   => $this->contract->id,
            "state"      => "draft",
            "date_start" => $this->flight->start_date,
            "date_end"   => $this->flight->end_date,
        ], pullRecord:   false);

        // This will hold the sum of production costs for each product category
        /** @var array<int, array<int, float>> $productionCosts Production cost accumulation cost
         * First index: Category ID; Second index: Cost; Value: Count
         */
        $productionCosts = [];

        // Now, we loop over each compiled product, and build its orderLines
        $orderLinesToAdd = collect();

        /** @var CPCompiledProduct $compiledProduct */
        foreach ($compiledProducts as $compiledProduct) {
            $dbProduct = $this->products->firstWhere("id", "=", $compiledProduct->id);

            if (!$dbProduct) {
                clock("Unknown product " . $compiledProduct->id);
                $messages[] = "Unknown product " . $compiledProduct->id;
                continue;
            }

            $orderLinesToAdd->push(...$this->buildLines($dbProduct, $compiledProduct));

            // Sum production costs
            if (isset($productionCosts[$compiledProduct->category_id])) {
                if (isset($productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value])) {
                    $productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value] += 1;
                } else {
                    $productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value] = 1;
                }
            } else {
                $productionCosts[$compiledProduct->category_id] = [$compiledProduct->production_cost_value => 1];
            }
        }

        $linesBatch = $orderLinesToAdd->chunk(100);

        // Creates all the lines
        foreach ($linesBatch as $batch) {
            OrderLine::createMany($client, $batch->toArray());
        }

        // Add the production costs
        // Load all the products categories
        $productCategories     = ProductCategory::query()->with("external_representations")
                                                ->findMany(array_keys($productionCosts));
        $productionLines       = [];
        $flightStartPlusOneDay = Carbon::createFromFormat("Y-m-d", $this->flight->start_date)->addDay()->toDateString();
        
        // Build the productions costs' lines
        foreach ($productCategories as $productCategory) {
            /** @var int|Optional $productionProductId */
            $productionProductId = $productCategory->external_representations
                ->firstWhere("inventory_id", "=", $this->odooInventoryId)?->context?->production_product_id;

            if (!$productionProductId || $productionProductId instanceof Optional) {
                clock("Missing production product Id for category #{$productCategory->getKey()}");
                $messages[] = "Missing production product Id for category #{$productCategory->getKey()} ({$productCategory->name_en})";
                continue;
            }

            // Load the product from id to get the variant id
            $productionProduct = OdooProduct::get($client, $productionProductId);

            if (!$productionProduct) {
                // Could not find production product, stop here
                $messages[] = "Could not find Odoo product with ID #{$productionProductId}";
                continue;
            }

            foreach ($productionCosts[$productCategory->getKey()] as $amount => $quantity) {
                $productionLines[] = [
                    "order_id"           => $this->contract->id,
                    "name"               => $productionProduct->display_name,
                    "price_unit"         => $amount,
                    "product_uom_qty"    => $quantity,
                    "customer_lead"      => 0.0,
                    "nb_screen"          => 1,
                    "product_id"         => $productionProduct->product_variant_id[0],
                    "rental_start"       => $this->flight->start_date,
                    "rental_end"         => $flightStartPlusOneDay,
                    "is_rental_line"     => 1,
                    "is_linked_line"     => 0,
                    "discount"           => 0,
                    "sequence"           => $this->flightIndex * 10,
                    "connect_impression" => 0,
                ];
            }
        }

        // Insert the production costs lines
        OrderLine::createMany($client, $productionLines);

        // And we are done.
        return $messages;
    }

    protected function buildLines(Product $product, CPCompiledProduct $compiledProduct): \Illuminate\Support\Collection {
        $orderLines = collect();

        /** @var ExternalInventoryResource|null $externalRepresentation */
        $externalRepresentation = $product->external_representations->firstWhere("inventory_id", "=", 1);

        // Cannot send product without a representation
        if (!$externalRepresentation) {
            // TODO: provide some feedback for this situation; let the user know which products where skipped
            return collect();
        }

        $orderLines->push([
                              "order_id"           => $this->contract->id,
                              "name"               => $product->name_en,
                              "price_unit"         => $compiledProduct->unit_price,
                              "product_uom_qty"    => $compiledProduct->spots,
                              "customer_lead"      => 0.0,
                              "nb_screen"          => $compiledProduct->quantity,
                              "product_id"         => $externalRepresentation->context->variant_id,
                              "rental_start"       => $this->flight->start_date,
                              "rental_end"         => $this->flight->end_date,
                              "is_rental_line"     => 1,
                              "is_linked_line"     => 0,
                              "discount"           => -$compiledProduct->discount_amount,
                              "sequence"           => $this->flightIndex * 10,
                              "connect_impression" => $compiledProduct->impressions,
                          ]);

        if (!$product->linked_product_id) {
            return $orderLines;
        }

        /** @var Product|null $linkedProduct */
        $linkedProduct = $this->products->firstWhere("id", "=", $product->linked_product_id);

        if (!$linkedProduct) {
            return $orderLines;
        }

        /** @var ExternalInventoryResource|null $externalRepresentation */
        $linkedProductExternalRepresentation = $linkedProduct->external_representations->firstWhere("inventory_id", "=", 1);

        // Cannot send product without a representation
        if (!$linkedProductExternalRepresentation) {
            return $orderLines;
        }

        $orderLines->push([
                              "order_id"        => $this->contract->id,
                              "name"            => $linkedProduct->name_en,
                              "price_unit"      => 0,
                              "product_uom_qty" => $compiledProduct->spots,
                              "customer_lead"   => 0.0,
                              "nb_screen"       => $linkedProduct->quantity,
                              "product_id"      => $linkedProductExternalRepresentation->context["variant_id"],
                              "rental_start"    => $this->flight->start_date,
                              "rental_end"      => $this->flight->end_date,
                              "is_rental_line"  => 1,
                              "is_linked_line"  => 1,
                              "discount"        => 0,
                              "sequence"        => $this->flightIndex * 10,
                          ]);

        return $orderLines;
    }
}
