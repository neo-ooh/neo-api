<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicDataReader.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports;

use GeoJson\GeoJson;
use Neo\Modules\Demographics\Structures\GeographicDataEntry;

interface GeographicDataReader {
    /**
     * Lists all matched areas
     * @return iterable<GeographicDataEntry>
     */
    public function getEntries(): iterable;

    /**
     * Returns the geometry shape used to compute the area.
     *
     * @return GeoJson|null
     */
    public function getGeometry(): ?GeoJson;
}
