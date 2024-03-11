<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProcessIndexSetsJob.php
 */

namespace Neo\Modules\Demographics\Jobs\IndexSets;

use JsonException;
use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\IndexSet;

/**
 * This job dispatches a `ProcessIndexSetJob` for each set in `ReportStatus::Pending` state
 */
class ProcessIndexSetsJob extends DemographicJobBase {

    public function __construct() {
    }

    /**
     * @throws InvalidFileFormatException
     * @throws UnsupportedFileFormatException
     * @throws JsonException
     */
    public function run(): mixed {
        $extracts = IndexSet::query()->where("status", "=", ReportStatus::Pending)->lazy(250);

        /** @var Extract $extract */
        foreach ($extracts as $extract) {
            ProcessIndexSetJob::dispatch($extract);
        }

        //Done
        return true;
    }
}
