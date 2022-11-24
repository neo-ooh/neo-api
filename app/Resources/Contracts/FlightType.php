<?php

namespace Neo\Resources\Contracts;

enum FlightType: string {
    case Guaranteed = "guaranteed";
    case Bonus = "bonus";
    case BUA = "bua";
}
