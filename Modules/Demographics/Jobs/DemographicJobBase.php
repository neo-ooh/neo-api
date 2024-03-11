<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicJobBase.php
 */

namespace Neo\Modules\Demographics\Jobs;

use Neo\Jobs\Job;
use Throwable;

abstract class DemographicJobBase extends Job {
    /**
     * @var int Allow infinite retry. The `Neo\Jobs\Job` superclass ensure work execution will not result in an exception leaking
     *          Infinite retry makes sure our rate limiter will not incur a loss of jobs
     */
    public int $tries = 0;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 1;

    public function middleware() {
        return [];
    }

    protected function onFailure(Throwable $exception): void {
      dump($exception);
    }
}
