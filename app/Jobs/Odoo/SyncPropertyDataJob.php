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
            $this->pullProductType($odooProductTypeId);
        }

        // Map each odoo product type id with Connect's ids
        $odooProductTypesMap = ProductType::query()
                                          ->whereIn("odoo_id", $odooProductTypesIds)
                                          ->get()
                                          ->mapWithKeys(fn($productType) => [$productType->odoo_id => $productType->id]);

        $products = [];

        // Now, store/update each product
        // for each product, we want to store its category, which is shared with other properties
        /** @var Product $distRentalProduct */
        foreach ($propertyRentalProducts as $distRentalProduct) {
            // Filter out bonus products
            if($distRentalProduct->bonus) {
                continue;
            }

            // Get or create the product category from our db
            $productCategory = $this->getProductCategory($distRentalProduct->categ_id[0], $odooProductTypesMap[$distRentalProduct->product_type_id[0]], $distRentalProduct->categ_id[1]);

            // Store or update the product in our db
            /** @var \Neo\Models\Odoo\Product $product */
            $product = \Neo\Models\Odoo\Product::query()->updateOrCreate([
                "property_id" => $odooProperty->property_id,
                "odoo_id" => $distRentalProduct->id,
            ], [
                "product_category_id" => $productCategory->id,
                "name" => $distRentalProduct->name,
                "odoo_variant_id" => $distRentalProduct->product_variant_id[0],
                "quantity" => $distRentalProduct->nb_screen,
                "unit_price" => $distRentalProduct->list_price,
            ]);

            $products[] = $product->odoo_id;
        }

        $odooProperty->products()->whereNotIn("odoo_id", $products)->delete();
    }

    protected function pullProductType(int $odooProductTypeId): void {
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

    protected function getProductCategory(int $odooCategoryId, int $productTypeId, string $internalName) {
        /** @var ProductCategory $productCategory */
        $productCategory                = ProductCategory::query()->firstOrNew([
            "odoo_id" => $odooCategoryId,
        ], [
            "name"            => $internalName,
            "product_type_id" => $productTypeId,
        ]);
        $productCategory->internal_name = $internalName;
        $productCategory->save();

        return $productCategory;
    }
}
