<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Group.php
 */

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Neo\Modules\Properties\Models\ProductCategory;

class Group {
    public GroupDefinition|null $group;

    /**
     * @var Collection<Collection<Property>>
     */
    public Collection $properties;

    public int $properties_count;
    public int $faces;
    public int $traffic;
    public int $impressions;
    public float $mediaValue;
    public float $mediaInvestment;
    public float $productionCost;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public function __construct(
        Collection $compiledProperties,
        array|null $groupDefinition
    ) {
        $this->group = $groupDefinition ? new GroupDefinition(
            $groupDefinition["name"],
            $groupDefinition["categories"],
            $groupDefinition["cities"],
            $groupDefinition["markets"],
            $groupDefinition["networks"],
            $groupDefinition["provinces"],
            $groupDefinition["tags"],
            $groupDefinition["color"],
        ) : null;

        $propertiesIds = $compiledProperties->pluck("id");

        // Pull all the properties for this group
        $properties = \Neo\Modules\Properties\Models\Property::query()
                                                             ->with(["network", "address"])
                                                             ->whereIn("actor_id", $propertiesIds)
                                                             ->get();

        // List all the product Ids, and pull them
        $productIdsChunks = $compiledProperties
            ->flatMap(fn($property) => collect($property["categories"])->flatMap(fn($category) => collect($category["products"])->pluck("id")))
            ->chunk(500);

        $products = collect();

        // Eloquent `whereIn` fails silently for references above ~1000 reference values
        foreach ($productIdsChunks as $chunk) {
            $products = $products->merge(\Neo\Modules\Properties\Models\Product::query()
                                                                               ->whereIn("id", $chunk)
                                                                               ->get());
        }


        $categories          = ProductCategory::query()->get();
        $formattedProperties = $compiledProperties
            ->map(fn(array $compiledProperty) => new Property($compiledProperty, $properties->firstWhere("actor_id", "=", $compiledProperty["id"]), $categories, $products))
            ->sortBy("property.actor.name")
            ->values();

        $this->properties = $formattedProperties;

        $this->properties_count = $this->properties->count();
        $this->faces            = $this->properties->sum("faces");
        $this->traffic          = $this->properties->sum("traffic");
        $this->impressions      = $this->properties->sum("impressions");
        $this->mediaValue       = $this->properties->sum("mediaValue");
        $this->mediaInvestment  = $this->properties->sum("mediaInvestment");
        $this->productionCost   = $this->properties->sum("productionCost");
        $this->price            = $this->properties->sum("price");
        $this->cpmPrice         = $this->properties->sum("cpmPrice");
        $this->cpm              = $this->impressions > 0 ? $this->cpmPrice / $this->impressions * 1000 : 0;

    }
}
