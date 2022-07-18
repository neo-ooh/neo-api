<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeNetworks.php
 */

namespace Neo\Jobs;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Services\Broadcast\Broadcast;

class SynchronizeNetworks extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Synchronized all networks' data in connect with their respective providers";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $networks = Network::all();

        foreach ($networks as $network) {
            $network = Broadcast::network($network->id);
            $network->synchronizeLocations();
            $network->synchronizePlayers();
        }

        return 0;
    }
}
