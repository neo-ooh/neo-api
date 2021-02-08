<?php

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
