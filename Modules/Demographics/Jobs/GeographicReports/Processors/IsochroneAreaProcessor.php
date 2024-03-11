<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RadiusAreaProcessor.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports\Processors;

use GeoJson\GeoJson;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Objects\Geometry;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Modules\Demographics\Jobs\GeographicReports\GeographicDataReader;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Structures\GeographicDataEntry;
use Neo\Services\Isochrone\IsochroneAdapter;

/**
 * This reader takes a point, a duration in minutes, a travel method to obtain an isochrone
 */
class IsochroneAreaProcessor implements GeographicDataReader {
    protected GeoJson|null $geometry = null;

    public function __construct(
        protected Point  $center,
        protected float  $durationMinutes,
        protected string $travelMethod,
        protected string $areaType = "FSALDU",
    ) {
    }

    public function getGeometry(): ?GeoJson {
        return $this->geometry;
    }

    public function getEntries(): iterable {
        // First thing we have to do is get the isochrone
        $provider       = app()->make(IsochroneAdapter::class);
        $this->geometry = $provider->getIsochrone(
            lng         : $this->center->longitude,
            lat         : $this->center->latitude,
            durationMin : $this->durationMinutes,
            travelMethod: $this->travelMethod,
        );

        $areas = Area::query()
                     ->whereRaw("ST_Within(\"geolocation\", ST_GeometryFromText(?))", [Geometry::fromArray($this->geometry->jsonSerialize())])
                     ->whereHas("type", function (Builder $query) {
                         $query->where("code", "=", $this->areaType);
                     })
                     ->orderBy("id")
                     ->with("type")
                     ->lazy(500);

        /** @var Area $area */
        foreach ($areas as $area) {
            yield new GeographicDataEntry(
                geography_type_code: $area->type->getKey(),
                geography_code     : $area->code,
                metadata           : [],
                geography_id       : $area->getKey(),
                weight             : 1
            );
        }
    }
}
