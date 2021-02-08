<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BlockJobsOnTestingEnv.php
 */

namespace Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\Middlewares;

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
    public function handle($job, $next)
    {
        if(config("app.env") !== "production") {
            $job->delete();
        } else {
            $next($job);
        }
    }
}
