<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PopulatePropertyDataJob.php
 */

namespace Neo\Jobs\Odoo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Neo\Models\Odoo\Product as OdooProduct;
use Neo\Models\Odoo\ProductType;
use Neo\Services\API\Odoo\Client;
use Neo\Services\Odoo\Models\Product;
use Neo\Services\Odoo\Models\Property;

class SyncPropertyDataJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected Client $client) {
    }

    public function handle() {
        $odooProperty = \Neo\Models\Odoo\Property::findOrFail($this->propertyId);

        // We are go, let's start by pulling the property from Odoo.
        $odooPropertyDist = Property::get($this->client, $odooProperty->odoo_id);

        // Property is filled, now we want to pull all the rental products of the property
        $propertyRentalProducts = Product::getMultiple($this->client, $odooPropertyDist->rental_product_ids);

        // Check if the product type are already correctly referenced
        $odooProductTypesIds = $propertyRentalProducts->pluck("product_type_id.0")->unique();

        foreach ($odooProductTypesIds as $odooProductTypeId) {
            $this->pullPropertyType($odooProductTypeId);
        }

        // Map each odoo product type id with connect's ids
        $odooProductTypesMap = ProductType::query()
                                          ->whereIn("odoo_id", $odooProductTypesIds)
                                          ->get()
                                          ->mapWithKeys(fn($productType) => [$productType->odoo_id => $productType->id]);

        $storedProducts = [];

        /** @var Product $rentalProduct */
        foreach ($propertyRentalProducts as $rentalProduct) {
            // Ignore bonus products
            if ($rentalProduct->bonus) {
                continue;
            }

            /** @var OdooProduct $product */
            $product                = OdooProduct::query()->firstOrNew([
                "property_id" => $odooProperty->property_id,
                "odoo_id"     => $rentalProduct->id,
            ], [
                "product_type_id" => $odooProductTypesMap[$rentalProduct->product_type_id[0]],
                "name"            => $rentalProduct->name
            ]);
            $product->internal_name = $rentalProduct->name;
            $product->save();

            $storedProducts[] = $product->id;
        }

        // Remove products not present anymore
        OdooProduct::query()->where("property_id", "=", $odooProperty->property_id)
                   ->whereNotIn("id", $storedProducts)
                   ->delete();
    }

    protected function pullPropertyType(int $odooProductTypeId) {
        if (ProductType::query()->where("odoo_id", "=", $odooProductTypeId)->exists()) {
            return;
        }

        // Pull the product type
        $productTypeDist = \Neo\Services\Odoo\Models\ProductType::get($this->client, $odooProductTypeId);

        $productType                = new ProductType();
        $productType->odoo_id       = $odooProductTypeId;
        $productType->name          = $productTypeDist->display_name;
        $productType->internal_name = $productTypeDist->name;

        $productType->save();
    }
}
