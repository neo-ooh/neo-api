<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CacheInventory.php
 */

namespace Neo\Console;


use Illuminate\Console\Command;
use Neo\BroadSign\Models\Inventory as BSInventory;
use Neo\BroadSign\Models\LoopPolicy;
use Neo\BroadSign\Models\Skin;
use Neo\Models\Inventory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job caches the Invenotry endpoint of BroadSign in the `inventory` table of the DB.
 * The goal of this is to leverage the computation needed to get actual meaningful information
 * from BroadSign Inventory report. This ensure minimal response time when querying availabilities.
 *
 * @package Neo\Jobs
 */
class CacheInventory extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:cache-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache availabilities for the entire network. Caches most of the intermediary queries for 1h. Running it more than once per hour is therefore useless.';

    public function handle(): void {
        collect([2020, 2021])->each(fn($year) => $this->cacheInventory($year));
    }

    protected function cacheInventory(int $year) {

        $this->info("Caching inventory for year $year...");

        $progressBar = $this->makeProgressBar(1);
        $progressBar->start();

        // Start by loading the latest version of the inventory
        $allInventory = BSInventory::all(["year" => $year]);

        $progressBar->setMaxSteps(count($allInventory));
        $progressBar->setMessage("Caching inventory...");

        // We now need to do some processing for each and every value output in order to extract meaningful values
        foreach ($allInventory as $inventory) {
            $progressBar->advance();

            // We need the skin and the loop policy associated with it
            /** @var Skin $skin */
            $skin       = Skin::get($inventory->skin_id);
            /** @var LoopPolicy $loopPolicy */
            $loopPolicy = LoopPolicy::get($skin->loop_policy_id);

            // Cache
            Inventory::query()->updateOrCreate([
                "skin_id" => $inventory->skin_id,
                "year" => $year,
            ], [
                "bookings" => $inventory->inventory,
                "max_booking" => $loopPolicy->max_duration_msec / $loopPolicy->default_slot_duration,
            ]);
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * @param int $steps
     *
     * @return ProgressBar
     */
    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
