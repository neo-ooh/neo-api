<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignJob.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Neo\BroadSign\Jobs\Middlewares\BlockJobOutsideProduction;

abstract class BroadSignJob implements ShouldQueue {
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware() {
        return [new BlockJobOutsideProduction()];
    }
}
