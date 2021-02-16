<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - NetworkUpdate.php
 */

namespace Neo\Console\Chores;

use Illuminate\Console\Command;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Jobs\DisableBroadSignCampaign;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\Models\Campaign;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class CleanUpCampaigns extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chores:campaigns-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up campaigns from BroadSign. Removing campaigns with no match in BroadSign';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $allCampaigns = BSCampaign::all();

        $activeCampaignsWithNoMatch = $allCampaigns
            ->filter(fn($campaign) => $campaign->parent_id === BroadSign::getDefaults()["customer_id"])
            ->filter(fn($c) => !Campaign::where("broadsign_reservation_id", "=", $c->id)->exists() && $c->active);

        foreach ($activeCampaignsWithNoMatch as $campaign) {
            DisableBroadSignCampaign::dispatchSync($campaign->id);
        }

        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
