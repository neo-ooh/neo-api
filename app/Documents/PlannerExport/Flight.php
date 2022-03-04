<?php

namespace Neo\Documents\PlannerExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Flight {
    public string|null $name;
    public Carbon $start;
    public Carbon $end;
    public float $length;
    public string $type;

    public Collection $groups;

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

        $this->groups = collect($compiledFlight['groups'])
            ->map(fn(array $group) => new Group(collect($group["properties"]), $group["group"]));

        $this->faces       = $compiledFlight["faces_count"];
        $this->traffic     = $compiledFlight["traffic"];
        $this->impressions = $compiledFlight["impressions"];
        $this->mediaValue  = $compiledFlight["media_value"];
        $this->price       = $compiledFlight["price"];
        $this->cpm         = $compiledFlight["cpm"];
        $this->cpmPrice    = $compiledFlight["cpmPrice"];
    }
}
