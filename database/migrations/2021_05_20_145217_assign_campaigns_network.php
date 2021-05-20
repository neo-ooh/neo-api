<?php

use Illuminate\Database\Migrations\Migration;
use Neo\Models\Campaign;
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
            $progressBar->setMessage($campaign->name . " (~" . $progressBar->getEstimated() . "s remaining)");

            $location = $campaign->locations()->first("network_id");

            $campaign->network_id = $location->network_id;
            $campaign->save();

            $progressBar->advance();
        }

        $progressBar->finish();
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
