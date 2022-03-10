<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_05_20_145217_assign_campaigns_network.php
 */

use Illuminate\Database\Migrations\Migration;
use Neo\Models\Campaign;
use Neo\Models\Container;
use Neo\Models\DisplayType;
use Neo\Models\Location;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // Start by syncing the networks data
        Artisan::call("network:sync");

        // Now, for each campaign, we need to look at its associated location, and apply the same network as the locations to the campaign
        $campaigns = Campaign::all();

        $progressBar = $this->makeProgressBar($campaigns->count());
        $progressBar->start();

        foreach ($campaigns as $campaign) {
            $progressBar->setMessage($campaign->name . " (" . $progressBar->getProgressPercent() . "%)");

            $location = $campaign->locations()->first(["network_id"]);

            if($location === null) {
                continue;
            }

            $campaign->network_id = $location->network_id;
            $campaign->save();

            $progressBar->advance();
        }

        $progressBar->setMessage("Done! \n");
        $progressBar->finish();

        // Clean up
        // Remove any location, player, display type not associated with a network
        Location::query()->whereNull("network_id")->delete();
        DisplayType::query()->whereNull("connection_id")->delete();
        Container::query()->whereNull("network_id")->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
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
};
