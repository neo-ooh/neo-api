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
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\DisplayType;
use Neo\Models\Location;
use Neo\Services\Broadcast\BroadSign\Models\Container;
use Neo\Services\Broadcast\BroadSign\Models\Format;
use Neo\Services\Broadcast\BroadSign\Models\Location as BSLocation;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class SynchronizeLocations extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $parsedLocations = [];

    public function uniqueId(): int {
        return $this->config->networkID;
    }

    public function handle(): void {
        (new ConsoleOutput())->writeLn("Synchronizing network {$this->config->networkUUID}...\n\n");

        // recursively parse containers
        $this->parseContainer($this->config->containerId);

        // Erase missing locations
        Location::query()
                ->whereNotIn("id", $this->parsedLocations)
                ->where("network_id", "=", $this->config->networkID)
                ->delete();
    }

    protected function parseContainer(int $containerId) {
        (new ConsoleOutput())->writeLn("Parsing container #$containerId...");
        $this->parseLocations(BSLocation::inContainer($this->getAPIClient(), $containerId));

        $containers = Container::inContainer($this->getAPIClient(), $containerId);

        foreach ($containers as $container) {
            if ($container->id === $containerId || $container->container_id !== $containerId) {
                continue;
            }

            $this->parseContainer($container->id);
        }
    }

    protected function parseLocations($broadSignLocations) {
        if (count($broadSignLocations) === 0) {
            return;
        }

        $progressBar = $this->makeProgressBar(count($broadSignLocations));
        $progressBar->start();

        /** @var BSLocation $bslocation */
        foreach ($broadSignLocations as $bslocation) {
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
                // Address could not be determined
                $address = "--";
                $city    = "--";
            }

            /** @var DisplayType $displayType */
            $displayType = $this->getDisplayType($bslocation->display_unit_type_id);

            // for now, we only identify locations by their externa ID and not their network to prevent erasing the currently stored locations and f***ing up the campaigns.
            // TODO: Once the deployement is good and everything is running, move the network_id assignment to the  identifying par of the request.
            /** @var Location $location */
            $location = Location::query()->firstOrCreate([
                "external_id" => $bslocation->id,
            ], [
                "name"          => $bslocation->name,
                "internal_name" => $bslocation->name,
            ]);

            $location->network_id      = $this->config->networkID;
            $location->display_type_id = $displayType->id;
            $location->container_id    = $containerID;
            $location->province        = $address;
            $location->city            = $city;
            $location->save();
            $this->parsedLocations[] = $location->id;

            /** @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }

        $progressBar->setMessage("Done.\n");
        $progressBar->finish();
    }

    protected function getDisplayType($displayTypeId) {
        // TODO: For now we ignore the connection Id associated with the display type as to not f***ck up existing resources. Once the migration to the new system is up and running, we'll add it back
        $displayType = DisplayType::query()
                                  ->where("external_id", "=", $displayTypeId)
//                                  ->where("connection_id", "=", $this->config->connectionID)
                                  ->first();

        if ($displayType) {
            // TODO: remove once previous todo is done.
            $displayType->connection_id = $this->config->connectionID;
            $displayType->save();

            return $displayType;
        }

        $bsDisplayType = Format::get($this->getAPIClient(), $displayTypeId);

        $displayType = new DisplayType([
            "connection_id" => $this->config->connectionID,
            "external_id"   => $bsDisplayType->id,
            "name"          => $bsDisplayType->name,
        ]);
        $displayType->save();

        return $displayType;
    }

}
