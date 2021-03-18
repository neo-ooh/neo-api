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
use Illuminate\Support\Facades\Date;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Inventory as BSInventory;
use Neo\BroadSign\Models\LoopPolicy;
use Neo\BroadSign\Models\ResourceCriteria;
use Neo\BroadSign\Models\Skin;
use Neo\Models\Inventory;
use Neo\Models\Location;
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

        $this->info("Cleaning up...");
        Inventory::query()->whereDate("updated_at", "<", Date::now()->subMinutes(30)->toDateString())->delete();

        $this->info("Inventory synced!");
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

            // Before doing anything, we want to check that the current skin has the advertising criteria associated with it
            $resourceCriteria = ResourceCriteria::forResource(["parent_id" => $inventory->skin_id]);

            // If none of the criteria applied to the skin is the advertising criteria, we skip it.
            if (!$resourceCriteria->some(fn($criteria) => $criteria->criteria_id === BroadSign::getDefaults()["advertising_criteria_id"])) {
                continue;
            }

            // Get the skin specified by the current report
            /** @var Skin $skin */
            $skin = Skin::get($inventory->skin_id);

            // Calculate the maximum booking allowed for the frame using its LoopPolicy
            /** @var LoopPolicy $loopPolicy */
            $loopPolicy = LoopPolicy::get($skin->loop_policy_id);

            $maxBooking = $loopPolicy->max_duration_msec / $loopPolicy->default_slot_duration;

            // Get its day part as we need the dates of the skin as well as a better name
            $dayPart = $skin->dayPart();

            if ($maxBooking === 0) {
                // Ignore inventories with no booking space.
                continue;
            }

            // Cache
            Inventory::query()->updateOrCreate([
                "skin_id" => $inventory->skin_id,
                "year"    => $year,
            ], [
                "location_id" => Location::query()->where("broadsign_display_unit", "=", $dayPart->parent_id)->first()->id,
                "name" => $dayPart->name,
                "start_date" => $dayPart->virtual_start_date,
                "end_date" => $dayPart->virtual_end_date,
                "bookings"    => $inventory->inventory,
                "max_booking" => $maxBooking,
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
