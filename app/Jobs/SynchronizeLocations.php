<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Location as BSLocation;
use Neo\Models\Format;
use Neo\Models\Location;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class SynchronizeLocations implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle (): void {
        $broadsignLocation = BSLocation::all();

        $locations = [];

        $progressBar = $this->makeProgressBar(count($broadsignLocation));
        $progressBar->start();

        /** @var BSLocation $bslocation */
        foreach ($broadsignLocation as $bslocation) {
            $progressBar->setMessage("{$bslocation->name} ($bslocation->id)");

            // Make sure the location's container is present in the DB
            $bsContainer = $bslocation->container;
            $containerID = null;

            if ($bsContainer !== null) {
                $bsContainer->replicate();
                $containerID = $bsContainer->id;
            }

            $location = Location::query()->firstOrCreate([
                "broadsign_display_unit" => $bslocation->id,
            ],
                [
                    "format_id"     => Format::query()->where("broadsign_display_type", "=", $bslocation->display_unit_type_id)
                                             ->first(["id"])->id,
                    "name"          => $bslocation->name,
                    "internal_name" => $bslocation->name,
                    "container_id"  => $containerID,
                ]);


            $locations[] = $location->id;

            $progressBar->advance();
        }

        $progressBar->setMessage("Locations syncing done!");
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");

        // Erase missing locations
        Location::whereNotIn("id", $locations)->delete();
    }

    /**
     * @param int $steps
     *
     * @return ProgressBar
     */
    protected function makeProgressBar (int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
