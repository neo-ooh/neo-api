<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizePropertyData.php
 */

namespace Neo\Jobs\Odoo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Neo\Models\ProductCategory;
use Neo\Models\ProductType;
use Neo\Models\Property;
use Neo\Services\Odoo\Models\Product;
use Neo\Services\Odoo\Models\ProductType as OdooProductType;
use Neo\Services\Odoo\Models\Property as OdooProperty;
use Neo\Services\Odoo\OdooClient;

class SynchronizePropertyData implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int           $propertyId,
                                protected OdooClient    $client,
                                protected ?OdooProperty $odooProperty = null,
                                protected ?Collection   $odooProducts = null) {
    }

    public function handle() {
        /** @var Property $property */
        $property = Property::findOrFail($this->propertyId);

        // Ignore property #252 as it is used for testing
        if ($property->getKey() === 252) {
            return;
        }

        // Check the property is matched with an odoo property
        if (!$property->odoo) {
            Log::warning("[SynchronizeProperty] Could not found property with ID #$this->propertyId");
            return;
        }

        // We are go, let's start by pulling the property from Odoo.
        $odooProperty = $this->odooProperty ?? OdooProperty::get($this->client, $property->odoo->odoo_id);

        if (!$odooProperty) {
            Log::warning("[SynchronizeProperty] [#$this->propertyId {$property->actor->name}] Could not found odoo property with ID #{$property->odoo->odoo_id}");
            return;
        }

        // We want to pull all the rental products of the property
        $propertyRentalProducts = Product::getMultiple($this->client, $odooProperty->rental_product_ids);

        // Make sure all the referenced product_types are present in the DB
        $odooProductTypesIds = $propertyRentalProducts->pluck("product_type_id.0")->unique();

        foreach ($odooProductTypesIds as $odooProductTypeId) {
            $this->pullProductType($odooProductTypeId);
        }

        // Map each odoo product type id with Connect's ids
        $odooProductTypesMap = $this->odooProducts ?? ProductType::query()
                                                                 ->whereIn("external_id", $odooProductTypesIds)
                                                                 ->get()
                                                                 ->mapWithKeys(fn($productType) => [$productType->external_id => $productType->id]);

        $products = [];

        // A product may be linked to another one. In this situation, we store the external ID reference, and enter those information in a second pass, when all products have been entered.
        $productsLinks = [];

        // Now, store/update each product
        // for each product, we want to store its category, which is shared with other properties
        /** @var Product $distRentalProduct */
        foreach ($propertyRentalProducts as $distRentalProduct) {
            // Get or create the product category from our db
            $productCategory = $this->getProductCategory($distRentalProduct->categ_id[0], $odooProductTypesMap[$distRentalProduct->product_type_id[0]], $distRentalProduct->categ_id[1]);

            // Store or update the product in our db
            /** @var \Neo\Models\Product $product */
            $product = \Neo\Models\Product::query()->firstOrNew([
                "external_id" => $distRentalProduct->id,
            ], [
                "property_id" => $property->getKey(),
            ]);

            $product->name_en             = $distRentalProduct->name;
            $product->name_fr             = $distRentalProduct->name;
            $product->category_id         = $productCategory->id;
            $product->quantity            = $distRentalProduct->nb_screen;
            $product->unit_price          = $distRentalProduct->list_price;
            $product->external_variant_id = $distRentalProduct->product_variant_id[0];
            $product->is_bonus            = (bool)$distRentalProduct->bonus;

            $product->save();

            if ($distRentalProduct->linked_product_id) {
                $productsLinks[] = [$product->getKey(), $distRentalProduct->linked_product_id[0]];
            }

            $products[] = $product->id;
        }

        // Perform update of linked products
        foreach ($productsLinks as [$productId, $externalLinkedId]) {
            DB::update(/** @lang MariaDB */ "
              UPDATE `products` AS `p1` 
              LEFT JOIN `products` AS `p2` ON `p2`.`external_id` = ?
              SET `p1`.`linked_product_id` = `p2`.`id`
              WHERE `p1`.`id` = ?", [$externalLinkedId, $productId]);
        }

        $property->products()->whereNotIn("id", $products)->delete();
    }

    protected function pullProductType(int $odooProductTypeId): void {
        if (ProductType::query()->where("external_id", "=", $odooProductTypeId)->exists()) {
            return;
        }

        // Pull the product type
        $productTypeDist = OdooProductType::get($this->client, $odooProductTypeId);

        if (!$productTypeDist) {
            return;
        }

        ProductType::query()->firstOrCreate([
            "external_id" => $odooProductTypeId,
        ], [
            "name_en" => $productTypeDist->display_name,
            "name_fr" => $productTypeDist->display_name,
        ]);
    }

    protected function getProductCategory(int $odooCategoryId, int $productTypeId, string $internalName) {
        /** @var ProductCategory $productCategory */
        $productCategory = ProductCategory::query()->firstOrCreate([
            "external_id" => $odooCategoryId,
        ], [
            "type_id" => $productTypeId,
            "name_en" => $internalName,
            "name_fr" => $internalName,
        ]);

        $productCategory->type_id = $productTypeId;
        $productCategory->save();

        return $productCategory;
    }
}
