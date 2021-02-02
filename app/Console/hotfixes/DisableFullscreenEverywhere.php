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
use Neo\BroadSign\Models\Bundle;
use Neo\Models\Campaign;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DisableFullscreenEverywhere extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:2020-02-02';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hotfix 2020-02-02 Disable fullscreen option on all bundles and ensure proper max duration';

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

        // Now for each campaign, we load its bundles, force-disable the fullscreen option, and save it
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $progressBar->advance();
            $progressBar->setMessage("{$campaign->name} ($campaign->broadsign_reservation_id)");

            $bundles = Bundle::byReservable($campaign->broadsign_reservation_id);

            /** @var Bundle $bundle */
            foreach ($bundles as $bundle) {
                $bundle->fullscreen        = false;
                $bundle->max_duration_msec = $campaign->display_duration * 1000;
                $bundle->save();
            }
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
