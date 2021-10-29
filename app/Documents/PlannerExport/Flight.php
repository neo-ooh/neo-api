<?php

namespace Neo\Documents\PlannerExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class Flight {
    public Carbon $startDate;
    public Carbon $endDate;
    public float $length;
    public string $type;
    public Collection $selection;

    public function __construct(array $flightRecord) {
        $this->startDate = Carbon::parse($flightRecord['start']);
        $this->endDate = Carbon::parse($flightRecord['end']);
        $this->length = $flightRecord['length'];
        $this->type = $flightRecord['type'];
        $this->selection = collect($flightRecord['selection']);
    }
}
