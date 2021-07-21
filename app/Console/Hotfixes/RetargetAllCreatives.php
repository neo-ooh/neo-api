<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RetargetAllCreatives.php
 */

namespace Neo\Console\Hotfixes;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Neo\Models\Creative;
use Neo\Models\Network;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Jobs\Creatives\TargetCreative;
use Neo\Services\Broadcast\BroadSign\Models\ResourceCriteria;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This script retargets all creatives scheduled on a BroadSign network
 *
 * @package Neo\Console\Hotfixes
 */
class RetargetAllCreatives extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadsign:retarget-creatives';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retarget all ad-copies';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $networks = Network::query()->whereHas("broadcaster_connection", function (Builder $query) {
            $query->where("broadcaster", "=", "broadsign");
        })->get();

        /** @var Network $network */
        foreach ($networks as $network) {
            $broadcastConfig = Broadcast::network($network->id)->getConfig();

            // Load all creatives who are scheduled in this network
            $creatives = Creative::query()->whereHas("external_ids", function (Builder $query) use ($network) {
                $query->where("network_id", "=", $network->id);
            })->get();


            $progressBar = $this->makeProgressBar($creatives->count());
            $progressBar->start();

            // Now for each creative, we deactivate all its applied criteria and force-target it again.
            /** @var Creative $creative */
            foreach ($creatives as $creative) {
                $externalId = $creative->getExternalId($network->id);

                $progressBar->advance();
                $progressBar->setMessage("Creative #($externalId) $creative->id");

                $criteria = ResourceCriteria::for(new BroadsignClient($broadcastConfig), $externalId);

                /** @var ResourceCriteria $criterion */
                foreach ($criteria as $criterion) {
                    $criterion->active = false;
                    $criterion->save();
                }

                TargetCreative::dispatchSync($broadcastConfig, $creative->id);
            }

            $progressBar->setMessage("$network->name creatives retargeted!");
            $progressBar->finish();
            (new ConsoleOutput())->writeln("");
        }

        $this->info("All Broadsign creatives have been re-targeted");
        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
