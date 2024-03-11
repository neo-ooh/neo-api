<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProcessExtractsJob.php
 */

namespace Neo\Modules\Demographics\Jobs\ProcessGeographicReportsJob;

use JsonException;
use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Jobs\GeographicReports\ProcessGeographicReportJob;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\GeographicReport;

/**
 * This job dispatches a `ProcessGeographicReport` for each geographic report in `ReportStatus::Pending` state
 */
class ProcessGeographicReportsJob extends DemographicJobBase {

    public function __construct() {
    }

    /**
     * @throws InvalidFileFormatException
     * @throws UnsupportedFileFormatException
     * @throws JsonException
     */
    public function run(): mixed {
        $reports = GeographicReport::query()->where("status", "=", ReportStatus::Pending)->lazy(250);

        /** @var GeographicReport $report */
        foreach ($reports as $report) {
            ProcessGeographicReportJob::dispatch($report);
        }

        //Done
        return true;
    }
}
