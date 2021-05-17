<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\DisplayType;
use Neo\Models\Location;
use Neo\Services\Broadcast\BroadSign\Models\Location as BSLocation;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class SynchronizeLocations extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        $broadsignLocation = BSLocation::all($this->getAPIClient());

        $locations = [];

        $progressBar = $this->makeProgressBar(count($broadsignLocation));
        $progressBar->start();

        /** @var BSLocation $bslocation */
        foreach ($broadsignLocation as $bslocation) {
            $progressBar->setMessage("$bslocation->name ($bslocation->id)");

            // Make sure the location's container is present in the DB
            $bsContainer = $bslocation->container;
            $containerID = null;

            if ($bsContainer !== null) {
                $bsContainer->replicate();
                $containerID = $bsContainer->id;
            }

            // Extract the province from the DisplayType address
            // Matches:
            // [0] => Full address
            // [1] => Street #
            // [2] => Street Name
            // [3] => City
            // [4] => Province
            // [5] => Zip code
            if (preg_match('/(^\d*)\s([.\-\w\s]+),\s*([.\-\w\s]+),\s*([A-Z]{2})\s(\w\d\w\s*\d\w\d)/iu', $bslocation->address, $matches)) {
                $address = trim($matches[4]);
                $city    = trim($matches[3]);
            } else {
//                Log::info("No address available for Display Unit $bslocation->name");
                $address = "--";
                $city    = "--";
            }

            /** @var DisplayType $displayType */
            $displayType = DisplayType::query()
                                      ->where("external_id", "=", $bslocation->display_unit_type_id)
                                      ->first();

            /** @var Location $location */
            $location = Location::query()->firstOrCreate([
                "external_id" => $bslocation->id,
            ], [
                "name"          => $bslocation->name,
                "internal_name" => $bslocation->name,
            ]);

            $location->display_type_id = $displayType->id;
            $location->container_id    = $containerID;
            $location->province        = $address;
            $location->city            = $city;
            $location->save();
            $locations[] = $location->id;

            /** @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }

        $progressBar->setMessage("Locations syncing done!");
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");

        // Erase missing locations
        Location::query()->whereNotIn("id", $locations)->delete();
    }
}
