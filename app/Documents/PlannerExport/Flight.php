<?php

namespace Neo\Documents\PlannerExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\Models\ProductCategory;

class Flight {
    public string|null $name;
    public Carbon $start;
    public Carbon $end;
    public float $length;
    public string $type;

    public Collection $properties;

    public int $faces;
    public int $traffic;
    public int $impressions;
    public float $mediaValue;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public function __construct(array $compiledFlight) {
        $this->name   = $compiledFlight["name"];
        $this->start  = Carbon::parse($compiledFlight['start']);
        $this->end    = Carbon::parse($compiledFlight['end']);
        $this->length = $compiledFlight['length'];
        $this->type   = $compiledFlight['type'];

        $propertiesIds = array_map(fn(array $property) => $property["id"], $compiledFlight["properties"]);
        $properties    = \Neo\Models\Property::query()->with(["network", "address"])->whereIn("actor_id", $propertiesIds)->get();
        $categories    = ProductCategory::query()->get();

        $productidsChunks = collect($compiledFlight["properties"])
            ->flatMap(fn($property) => collect($property["categories"])->flatMap(fn($category) => collect($category["products"])->pluck("id")))
            ->chunk(500);

        $products = collect();

        // Eloquent `whereIn` fails silently for references above ~1000 reference values
        foreach ($productidsChunks as $chunk) {
            $products = $products->merge(\Neo\Models\Product::query()
                                                            ->whereIn("id", $chunk)
                                                            ->get());
        }

        $this->properties = collect($compiledFlight['properties'])->map(fn(array $property) => new Property($property, $properties->firstWhere("actor_id", "=", $property["id"]), $categories, $products));

        $this->faces       = $compiledFlight["faces_count"];
        $this->traffic     = $compiledFlight["traffic"];
        $this->impressions = $compiledFlight["impressions"];
        $this->mediaValue  = $compiledFlight["media_value"];
        $this->price       = $compiledFlight["price"];
        $this->cpm         = $compiledFlight["cpm"];
        $this->cpmPrice    = $compiledFlight["cpmPrice"];
    }
}
