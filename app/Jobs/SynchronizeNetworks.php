<?php

namespace Neo\Jobs;

use Illuminate\Console\Command;
use Neo\Models\Network;
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
    public function handle() {
        $networks = Network::all(["id", "name"]);

        foreach ($networks as $network) {
            $network = Broadcast::network($network->id);
            $network->synchronizeLocations();
            $network->synchronizeFormats();
        }
    }
}
