<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetValuesExtractor.php
 */

namespace Neo\Modules\Demographics\Jobs\Extracts\DatasetValuesExtractors;

use Illuminate\Database\Connection;
use Neo\Modules\Demographics\Models\DatasetDatapoint;

interface DatasetValuesExtractor {
    /**
     * Perform an extraction for a single datapoint against a geographic report and insert it as a value for the specified extract ID
     * 
     * @param Connection       $db
     * @param DatasetDatapoint $datapoint
     * @param int              $geographyReportId
     * @param int              $extractId
     * @return mixed
     */
    public static function extract(Connection $db, DatasetDatapoint $datapoint, int $geographyReportId, int $extractId);
}

