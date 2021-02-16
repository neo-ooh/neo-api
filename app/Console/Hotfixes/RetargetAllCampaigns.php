<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - NetworkUpdate.php
 */

namespace Neo\Console\Hotfixes;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\UpdateCampaignTargeting;
use Neo\BroadSign\Models\Bundle;
use Neo\BroadSign\Models\Location;
use Neo\Models\Campaign;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class RetargetAllCampaigns extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:2021-02-16';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retarget all campaigns by removing all their associated frames and associating them again in BroadSign.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // We start by loading all campaigns on connect who have a counterpart on BroadSign
        $campaigns = Campaign::query()->whereNotNull('broadsign_reservation_id')->get();

        $progressBar = $this->makeProgressBar(count($campaigns));
        $progressBar->start();

        // Now for each campaign, we load its locations, remove them, and trigger a re-targeting
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $progressBar->advance();
            $progressBar->setMessage("{$campaign->name} ($campaign->broadsign_reservation_id)");

            $locations = Location::byReservable(["reservable_id" => $campaign->broadsign_reservation_id]);
            \Neo\BroadSign\Models\Campaign::dropSkinSlots([
                "id"           => $campaign->broadsign_reservation_id,
                "sub_elements" => [
                    "display_unit" => $locations->map(fn($du) => ["id" => $du->broadsign_display_unit])->values()->toArray(),
                ]
            ]);

            // Trigger a targeting of the campaign
            UpdateCampaignTargeting::dispatchSync($campaign->id);
        }

        $progressBar->setMessage("Hotfix done");
        $progressBar->finish();

        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
