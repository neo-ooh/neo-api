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
use Illuminate\Support\Facades\Log;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Jobs\Campaigns\DisableBroadSignCampaign;
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
    protected $description = 'Clean up campaigns from BroadSign. Removing campaigns in specific trash folder';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $allCampaigns = BSCampaign::all();

        $campaignToDelete = \Neo\BroadSign\Models\Campaign::inContainer(437269513);

        foreach ($campaignToDelete as $campaign) {
            DisableBroadSignCampaign::dispatchSync($campaign->id);
        }

        return 0;
    }
}
