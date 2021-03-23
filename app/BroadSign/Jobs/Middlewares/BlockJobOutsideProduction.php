<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BlockJobOutsideProduction.php
 */

namespace Neo\BroadSign\Jobs\Middlewares;

use Illuminate\Queue\Jobs\Job;

/**
 * Class BlockJobOnTestingEnv
 *
 * @package Neo\BroadSign\Jobs\Middlewares
 */
class BlockJobOutsideProduction
{
    /**
     * Process the queued job.
     *
     * @param  Job|mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next): void
    {
        if(config("app.env") !== "production") {
            $job->delete();
        } else {
            $next($job);
        }
    }
}
