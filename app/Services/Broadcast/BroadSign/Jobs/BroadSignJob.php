<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignJob.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\Middlewares\BlockJobOutsideProduction;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class BroadSignJob implements ShouldQueue {

    protected BroadSignConfig $config;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware() {
        return [new BlockJobOutsideProduction()];
    }

    public function __construct(BroadSignConfig $config) {
        $this->config = $config;
    }

    public function getAPIClient(): BroadsignClient {
        return new BroadsignClient($this->config);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Create a Symfony console progress bar ready to be used!
     *
     * @param int $steps
     * @return ProgressBar
     */
    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
