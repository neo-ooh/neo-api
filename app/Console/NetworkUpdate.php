<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - NetworkUpdate.php
 */

namespace Neo\Console;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizeFormats;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizeLocations;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizePlayers;

class NetworkUpdate extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Network information, using data from the BroadSign API.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle (): int {
        SynchronizeFormats::dispatchSync();
        SynchronizeLocations::dispatchSync();
        SynchronizePlayers::dispatchSync();
        return 0;
    }
}
