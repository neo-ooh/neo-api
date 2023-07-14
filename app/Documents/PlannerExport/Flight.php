<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Flight.php
 */

namespace Neo\Documents\PlannerExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Flight {
    public string|null $name;
    public Carbon $start;
    public Carbon $end;
    public float $length;
    public string $type;

    /**
     * @var Collection<Group>
     */
    public Collection $groups;

    public int $faces;
    public int $traffic;
    public int $impressions;
    public float $mediaValue;
    public float $mediaInvestment;
    public float $productionCost;
    public float $price;
    public float $cpm;
    public float $cpmPrice;

    public function __construct(array $compiledFlight) {
        $this->name   = $compiledFlight["name"];
        $this->start  = Carbon::parse($compiledFlight['start_date']);
        $this->end    = Carbon::parse($compiledFlight['end_date']);
        $this->length = $compiledFlight['length'];
        $this->type   = $compiledFlight['type'];

        $this->groups = collect($compiledFlight['groups'])
            ->map(fn(array $group) => new Group(collect($group["properties"]), $group["group"]));

        $this->faces           = $compiledFlight["faces_count"];
        $this->traffic         = $compiledFlight["traffic"];
        $this->impressions     = $compiledFlight["impressions"];
        $this->mediaValue      = $compiledFlight["media_value"];
        $this->mediaInvestment = $compiledFlight["discounted_media_value"];
        $this->productionCost  = $compiledFlight["production_cost_value"];
        $this->price           = $compiledFlight["price"];
        $this->cpm             = $compiledFlight["cpm"];
        $this->cpmPrice        = $compiledFlight["cpmPrice"];
    }
}
