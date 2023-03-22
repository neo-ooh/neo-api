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

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\Product;
use Neo\Resources\Contracts\CPCompiledCategory;
use Neo\Resources\Contracts\CPCompiledFlight;
use Neo\Resources\Contracts\CPCompiledProduct;
use Neo\Resources\Contracts\CPCompiledProperty;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;

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
    public function handle(): void {
        $client = OdooConfig::fromConfig()->getClient();

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

        // Now, we loop over each compiled product, and build its orderLines
        $orderLinesToAdd = collect();

        /** @var CPCompiledProduct $compiledProduct */
        foreach ($compiledProducts as $compiledProduct) {
            $dbProduct = $this->products->firstWhere("id", "=", $compiledProduct->id);

            if (!$dbProduct) {
                clock("Unknown product " . $compiledProduct->id);
                continue;
            }

            $orderLinesToAdd->push(...$this->buildLines($dbProduct, $compiledProduct));
        }

        $linesBatch = $orderLinesToAdd->chunk(100);

        foreach ($linesBatch as $batch) {
            OrderLine::createMany($client, $batch->toArray());
        }

        // And we are done.
    }

    protected function buildLines(Product $product, CPCompiledProduct $compiledProduct): \Illuminate\Support\Collection {
        $orderLines = collect();

        $discountAmount = $compiledProduct->media_value > 0 ? (1 - ($compiledProduct->price / $compiledProduct->media_value)) * 100 : 0;

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
                              "product_id"         => $externalRepresentation->context["variant_id"],
                              "rental_start"       => $this->flight->start_date,
                              "rental_end"         => $this->flight->end_date,
                              "is_rental_line"     => 1,
                              "is_linked_line"     => 0,
                              "discount"           => $discountAmount,
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
