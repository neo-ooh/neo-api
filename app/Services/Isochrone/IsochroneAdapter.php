<?php

namespace Neo\Services\Isochrone;

interface IsochroneAdapter {
    public function getIsochrone(
        float  $lng,
        float  $lat,
        int    $durationMin,
        string $travelMethod
    );
}