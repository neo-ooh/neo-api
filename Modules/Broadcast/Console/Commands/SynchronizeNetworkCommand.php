<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeNetworkCommand.php
 */

namespace Neo\Modules\Broadcast\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Jobs\Networks\SynchronizeNetworkJob;

class SynchronizeNetworkCommand extends Command {
    protected $signature = 'network:sync {network}';

    protected $description = 'Command description';

    public function handle() {
        $job = new SynchronizeNetworkJob($this->argument("network"), $this->getOutput()->getOutput());

        $job->handle();

        return 0;
    }
}
