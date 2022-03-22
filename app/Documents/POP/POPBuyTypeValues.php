<?php

namespace Neo\Documents\POP;

use Carbon\Carbon;
use Illuminate\Support\Collection;


class POPBuyTypeValues {
    public string $type;
    public bool $show;

    /**
     * @var Collection<POPTypeNetworkValues>
     */
    public Collection $networks;

    public Carbon $start_date;
    public Carbon $end_date;

    public float $contracted_impressions;
    public float $media_value;
    public float $net_investment;

    public float $counted_impressions;

    public function __construct(array $data) {
        $this->type = $data["type"];
        $this->show = (bool)$data["show"];

        $this->networks = collect($data["networks"])->map(fn(array $values) => new POPTypeNetworkValues($values));

        if ($this->networks->count() === 0) {
            return;
        }

        $this->start_date = $this->networks->min("start_date");
        $this->end_date   = $this->networks->max("end_date");

        $this->contracted_impressions = $this->networks->sum("contracted_impressions");
        $this->media_value            = $this->networks->sum("media_value");
        $this->net_investment         = $this->networks->sum("net_investment");
        $this->counted_impressions    = $this->networks->sum(fn(POPTypeNetworkValues $networkValues) => $networkValues->received_impressions * $networkValues->adjustment_factor);
    }
}

