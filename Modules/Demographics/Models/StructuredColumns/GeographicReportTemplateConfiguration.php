<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportTemplateConfiguration.php
 */

/** @noinspection PhpOptionalBeforeRequiredParametersInspection */

namespace Neo\Modules\Demographics\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateAreaType;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateTargetingType;
use Spatie\LaravelData\Optional;

class GeographicReportTemplateConfiguration extends JSONDBColumn {
    public function __construct(
        // Targeting
        public int                                       $weight,
        public GeographicReportTemplateTargetingType     $targeting,
        public array                                     $target_ids,

        // Parameters
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
         * Either 'km' for radius distance, or 'km' or 'minutes' for isochrones
         * @var string|Optional
         */
        public string|Optional $distance_unit,

        /**
         * For isochrones, routing profile to use, can be either `driving`, `walking` or `cycling`
         * @var string|Optional
         */
        public string|Optional $routing,
    ) {

    }
}
