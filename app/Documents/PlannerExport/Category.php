<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Category.php
 */

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Neo\Modules\Properties\Models\ProductCategory;

class Category {
    public int $id;
    public ProductCategory $category;

    public int $faces;
    public int $impressions;
    public float $mediaValue;
    public float $mediaInvestment;
    public float $productionCost;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public Collection $products;

    public function __construct(array $compiledCategory, ProductCategory $category, Collection $products) {
        $this->id = $compiledCategory["id"];

        $this->category = $category;
        $this->products = collect($compiledCategory['products'])->map(function (array $product) use ($products) {
            $dbproduct = $products->firstWhere("id", $product["id"]);

            if (!$dbproduct) {
                return null;
            }

            return new Product($product, $dbproduct);
        })->whereNotNull();

        $this->faces           = $compiledCategory["faces_count"];
        $this->impressions     = $compiledCategory["impressions"];
        $this->mediaValue      = $compiledCategory["media_value"];
        $this->mediaInvestment = $compiledCategory["discounted_media_value"];
        $this->productionCost  = $compiledCategory["production_cost_value"];
        $this->price           = $compiledCategory["price"];
        $this->cpm             = $compiledCategory["cpm"];
        $this->cpmPrice        = $compiledCategory["cpmPrice"];
    }
}
