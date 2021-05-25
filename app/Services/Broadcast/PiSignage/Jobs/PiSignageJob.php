<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignJob.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Neo\Services\Broadcast\PiSignage\API\PiSignageClient;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class PiSignageJob implements ShouldQueue {

    protected PiSignageConfig $config;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware() {
        return [
//            new BlockJobOutsideProduction()
        ];
    }

    public function __construct(PiSignageConfig $config) {
        $this->config = $config;
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public function getAPIClient(): PiSignageClient {
        return new PiSignageClient($this->config);
    }

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
