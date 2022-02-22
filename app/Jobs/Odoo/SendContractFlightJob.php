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
            $dbproduct = $this->products->firstWhere("id", "=", $compiledProduct["id"]);

            if (!$dbproduct) {
                continue;
            }

            $orderLinesToAdd->push(...$this->buildLines($dbproduct, $compiledProduct));
        }

        $sendGroups = $orderLinesToAdd->split(max(1, $orderLinesToAdd->count() / 100));

        foreach ($sendGroups as $sendGroup) {
            clock($client->client->call(OrderLine::$slug, "create", [$sendGroup->toArray()]));
        }

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
}
