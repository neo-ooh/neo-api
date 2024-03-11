<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportMetadata.php
 */

namespace Neo\Modules\Demographics\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateAreaType;
use Spatie\LaravelData\Optional;

class GeographicReportMetadata extends JSONDBColumn {
    public function __construct(
        // For AREA reports
        /**
         * @var GeographicReportTemplateAreaType|Optional
         */
        public GeographicReportTemplateAreaType|Optional $area_type,

        /**
         * Used for calculating the area size, unit specified in the `distance_unit` property
         *
         * @var float|Optional
         */
        public float|Optional $distance,

        /**
         * Either Km for radius distance, or Km or meters for isochrones
         * @var string|Optional
         */
        public string|Optional $distance_unit,

        /**
         * For isochrones, routing profile to use, can be either `driving`, `walking` or `cycling`
         * @var string|Optional
         */
        public string|Optional $routing,

        /**
         * @var array|Optional For isochrones and custom areas, stores the actual area as GeoJSON
         */
        public array|Optional $area,

        // For CUSTOMER reports
        public string|Optional $source_file,
        public string|Optional $source_file_type,
        public string|Optional $source_file_format,



        // To store potential errors
        public array|Optional $error,
    ) {

    }
}
