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
use Neo\Models\Odoo\ProductCategory;
use Neo\Models\Odoo\ProductType;
use Neo\Services\API\Odoo\Client;
use Neo\Services\Odoo\Models\Product;
use Neo\Services\Odoo\Models\Property;

class SyncPropertyDataJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected Client $client) {
    }

    public function handle() {
        /** @var \Neo\Models\Odoo\Property $odooProperty */
        $odooProperty = \Neo\Models\Odoo\Property::findOrFail($this->propertyId);

        // We are go, let's start by pulling the property from Odoo.
        $odooPropertyDist = Property::get($this->client, $odooProperty->odoo_id);

        // We want to pull all the rental products of the property
        $propertyRentalProducts = Product::getMultiple($this->client, $odooPropertyDist->rental_product_ids);

        // Make sure all the referenced product_types are present in the DB
        $odooProductTypesIds = $propertyRentalProducts->pluck("product_type_id.0")->unique();

        foreach ($odooProductTypesIds as $odooProductTypeId) {
            $this->pullPropertyType($odooProductTypeId);
        }

        // Map each odoo product type id with Connect's ids
        $odooProductTypesMap = ProductType::query()
                                          ->whereIn("odoo_id", $odooProductTypesIds)
                                          ->get()
                                          ->mapWithKeys(fn($productType) => [$productType->odoo_id => $productType->id]);

        // for each product, we want to store its category, which is shared with other properties, and link it properly with the property
        $productCategoriesIds = [];

        /** @var Product $distRentalProduct */
        foreach ($propertyRentalProducts as $distRentalProduct) {
            // Get or create the product category from our db
            /** @var ProductCategory $productCategory */
            $productCategory                = ProductCategory::query()->firstOrNew([
                "odoo_id" => $distRentalProduct->categ_id[0],
            ], [
                "name"            => $distRentalProduct->categ_id[1],
                "product_type_id" => $odooProductTypesMap[$distRentalProduct->product_type_id[0]],
                "quantity"        => 0,
            ]);
            $productCategory->internal_name = $distRentalProduct->categ_id[1];
            $productCategory->quantity      = $productCategory->quantity + $distRentalProduct->nb_screen;
            $productCategory->save();

            $productCategoriesIds[] = $productCategory->id;
        }

        $odooProperty->products_categories()->sync($productCategoriesIds);
    }

    protected function pullPropertyType(int $odooProductTypeId): void {
        if (ProductType::query()->where("odoo_id", "=", $odooProductTypeId)->exists()) {
            return;
        }

        // Pull the product type
        $productTypeDist = \Neo\Services\Odoo\Models\ProductType::get($this->client, $odooProductTypeId);

        ProductType::query()->firstOrCreate([
            "odoo_id" => $odooProductTypeId,
        ], [
            "name"          => $productTypeDist->display_name,
            "internal_name" => $productTypeDist->name,
        ]);
    }
}
