<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;

class Property {
    public int $propertyId;
    public \Neo\Models\Property $property;

    public int $faces;
    public int $traffic;
    public int $impressions;
    public float $mediaValue;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public Collection $categories;

    public function __construct(array $compiledProperty, \Neo\Models\Property $property, \Illuminate\Database\Eloquent\Collection $categories, \Illuminate\Database\Eloquent\Collection $products) {
        $this->propertyId = $compiledProperty["id"];

        $this->property   = $property;
        $this->categories = collect($compiledProperty['categories'])->map(fn(array $category) => new Category($category, $categories->firstWhere("id", "=", $category["id"]), $products));

        $this->faces       = $compiledProperty["faces_count"];
        $this->traffic     = $compiledProperty["traffic"];
        $this->impressions = $compiledProperty["impressions"];
        $this->mediaValue  = $compiledProperty["media_value"];
        $this->price       = $compiledProperty["price"];
        $this->cpm         = $compiledProperty["cpm"];
        $this->cpmPrice    = $compiledProperty["cpmPrice"];
    }
}
