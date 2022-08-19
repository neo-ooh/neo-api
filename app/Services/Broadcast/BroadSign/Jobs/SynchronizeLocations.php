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
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Container;
use Neo\Modules\Broadcast\Services\BroadSign\Models\DisplayType;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Location as BSLocation;
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
            // We want to make sure we are not getting a container that is not the child of the current one, or is the current one.
            // This is to prevent infinite loops.
            if ($container->id === $containerId || $container->container_id !== $containerId) {
                continue;
            }

            $this->parseContainer($container->id);
        }
    }

    protected function parseLocations($broadSignLocations) {
        if (!$broadSignLocations || count($broadSignLocations) === 0) {
            return;
        }

        $progressBar = $this->makeProgressBar(count($broadSignLocations));
        $progressBar->start();

        $bsContainer = $broadSignLocations[0]->getContainer();

        // Make sure the location's container is present in the DB
        $containerID = null;

        if ($bsContainer !== null) {
            $bsContainer->replicate($this->config->networkID);
            $containerID = $containerID !== $this->config->containerId ? $bsContainer->id : null;
        }

        /** @var BSLocation $bslocation */
        foreach ($broadSignLocations as $bslocation) {
            $progressBar->setMessage("$bslocation->name ($bslocation->id)");

            // Ignore deactivated broadsign units
            if (!$bslocation->active) {
                continue;
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
            /** @var Location $location */
            $location = Location::query()->firstOrCreate([
                "external_id" => $bslocation->id,
                "network_id"  => $this->config->networkID,
            ]);

            $location->name            = $bslocation->name;
            $location->internal_name   = $bslocation->name;
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

    /**
     * Get the `DisplayType` for the given BroadSign external Id.
     * If no display type exist, a new one is created for the current connection.
     *
     * @param $displayTypeId
     * @return DisplayType
     */
    protected function getDisplayType($displayTypeId) {
        /** @var \Neo\Modules\Broadcast\Models\DisplayType $displayType */
        $displayType = DisplayType::query()->firstOrNew([
            "connection_id" => $this->config->connectionID,
            "external_id"   => $displayTypeId
        ]);

        $bsDisplayType = DisplayType::get($this->getAPIClient(), $displayTypeId);

        $displayType->internal_name = $bsDisplayType->name;

        if (!$displayType->exists) {
            $displayType->name = $bsDisplayType->name;
        }

        $displayType->save();
        return $displayType;
    }

}
