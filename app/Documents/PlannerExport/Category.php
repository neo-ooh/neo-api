<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Neo\Models\ProductCategory;

class Category {
    public int $id;
    public ProductCategory $category;

    public int $faces;
    public int $impressions;
    public float $mediaValue;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public Collection $products;

    public function __construct(array $compiledCategory, ProductCategory $category, \Illuminate\Database\Eloquent\Collection $products) {
        $this->id = $compiledCategory["id"];

        $this->category = $category;
        $this->products = collect($compiledCategory['products'])->map(fn(array $product) => new Product($product, $products->firstWhere("id", $product["id"])));

        $this->faces       = $compiledCategory["faces_count"];
        $this->impressions = $compiledCategory["impressions"];
        $this->mediaValue  = $compiledCategory["media_value"];
        $this->price       = $compiledCategory["price"];
        $this->cpm         = $compiledCategory["cpm"];
        $this->cpmPrice    = $compiledCategory["cpmPrice"];
    }
}
