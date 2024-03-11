<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicDataEntry.php
 */

/** @noinspection PhpOptionalBeforeRequiredParametersInspection */

namespace Neo\Modules\Demographics\Structures;

use Spatie\LaravelData\Data;

/**
 * Used when generating a Geographic report to represent geographic areas to insert in the report values.
 */
class GeographicDataEntry extends Data {
    public function __construct(
        /**
         * Code of the area type
         * e.g. FSALDU, CMACD, etc.
         *
         * @var string
         */
        public string $geography_type_code,

        /**
         * Unique code for the geography based on its type
         *
         * @var string
         */
        public string $geography_code,

        /**
         * Any additional data that should be kept with this entry.
         *
         * @var array
         */
        public array $metadata,

        /**
         * ID of the geography in the DemographicDB if it is already known
         *
         * @var int|null
         */
        public int|null $geography_id = null,

        /**
         * Weight of the geography against other geography in the report.
         * If no weight are applied, this should be set to 1.0
         *
         * @var float
         */
        public float $weight = 1.0,
    ) {

    }
}
