<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CustomAreaProcessor.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports\Processors;

use GeoJson\GeoJson;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Objects\Geometry;
use Neo\Modules\Demographics\Jobs\GeographicReports\GeographicDataReader;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Structures\GeographicDataEntry;
use Override;

/**
 * This reader takes a geometry, and returns a list of all areas of the desired type inside the radius.
 */
class CustomAreaProcessor implements GeographicDataReader {
    public function __construct(protected Geometry $geometry, protected string $areaType = "FSALDU") {
    }

    public function getEntries(): iterable {
        $areas = Area::query()
            ->whereRaw("ST_Within(\"geolocation\", ST_GeometryFromText(?))", [$this->geometry])
            ->whereHas("type", function (Builder $query) {
                $query->where("code", "=", $this->areaType);
            })
            ->orderBy("id")
            ->with("type")
            ->lazy(500);


        /** @var Area $area */
        foreach ($areas as $area) {
            yield new GeographicDataEntry(
                geography_id: $area->getKey(),
                geography_type_code: $area->type->getKey(),
                geography_code: $area->code,
                weight: 1,
                metadata: []
            );
        }
    }

    #[Override] public function getGeometry(): ?GeoJson {
        return null;
    }
}
