<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeNetworkJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Networks;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\NetworkContainer;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterContainers;
use Neo\Modules\Broadcast\Services\BroadcasterLocations;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\Resources\Container as ContainerResource;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Location as LocationResource;
use Neo\Modules\Broadcast\Services\Resources\Player as PlayerResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Synchronize a network' locations and players with its broadcaster
 *
 * @extends Job<null>
 */
class SynchronizeNetworkJob extends Job {
    public function __construct(protected int $networkId) {
    }

    /**
     * Steps here:
     * 1. List all locations for network
     * 2. List all players for network
     * 3. Register/update location in Connect, replicating containers (if applicable) and players
     *
     * @throws InvalidBroadcasterAdapterException|UnknownProperties
     */
    protected function run(): mixed {
        $output = new ConsoleOutput();

        /** @var BroadcasterOperator&BroadcasterLocations&BroadcasterContainers $broadcaster */
        $broadcaster = BroadcasterAdapterFactory::makeForNetwork($this->networkId);

        // Make sure the broadcaster supports locations
        if (!$broadcaster->hasCapability(BroadcasterCapability::Locations)) {
            // No, stop here
            $output->writeln("<error>Network #$this->networkId does not support locations</error>");
            return null;
        }

        // List all players from the network
        $externalPlayers   = collect($broadcaster->listPlayers());
        $externalLocations = $broadcaster->listLocations();

        // Some broadcaster may simply stop returning deleted players, so we keep a list of the players we found, and delete the other one at the end
        $locations = [];

        $locationLine = $output->section();
        $log          = $output->section();
        $log->write("<comment>Starting...</comment>");

        // Now, parse each location
        /** @var LocationResource $externalLocation */
        foreach ($externalLocations as $externalLocation) {
            $log->clear();
            $locationLine->writeln("<info>[Location #$externalLocation->external_id - $externalLocation->name]</info>");

            // Get the display type of the location
            $log->writeln("<comment>Getting display type...</comment>");
            $displayType = $this->getDisplayType($broadcaster, $externalLocation->external_display_type_id);

            if (!$displayType) {
                // Could not find a display type, ignore
                $locationLine->writeln("<comment>No display type available, ignoring location</comment>");
                continue;
            }

            // Get the container ID for the location
            if ($broadcaster->hasCapability(BroadcasterCapability::Containers)) {
                $log->writeln("<comment>Persist containers...</comment>");
                $containerId = $this->persistContainersHierarchy($broadcaster, $externalLocation->container_id);
            } else {
                $containerId = null;
            }

            // If the location is deactivated, and it does not exist in Connect, short-circuit here and prevent its creation
            if (!$externalLocation->enabled && Location::query()->where("network_id", "=", $broadcaster->getNetworkId())
                                                       ->where("external_id", "=", $externalLocation->external_id)
                                                       ->doesntExist()) {
                $locationLine->writeln("<comment>Location is not enabled and is not in connect, ignore.</comment>");
                continue;
            }

            $log->writeln("<comment>Persist location...</comment>");

            // Insert the location in the DB
            /** @var Location $location */
            $location = Location::withTrashed()->updateOrCreate([
                "network_id"  => $broadcaster->getNetworkId(),
                "external_id" => $externalLocation->external_id,
            ], [
                "display_type_id" => $displayType->getKey(),
                "internal_name"   => $externalLocation->name,
                "name"            => $externalLocation->name,
                "container_id"    => $containerId,
            ]);

            if ($externalLocation->enabled && $location->trashed()) {
                $location->restore();
            } else if (!$externalLocation->enabled && !$location->trashed()) {
                $location->delete();
            }

            $locations[] = $location->getKey();

            // List players for this location
            $externalLocationPlayers = $externalPlayers->filter(fn(PlayerResource $player) => $player->location_id->external_id === $externalLocation->external_id);

            // Some broadcaster may simply stop returning deleted players, so we keep a list of the players we found, and delete the other one at the end
            $players = [];

            $log->writeln("<comment>Synchronize players... </comment>");

            /** @var PlayerResource $externalPlayer */
            foreach ($externalLocationPlayers as $externalPlayer) {
                // If the player is deactivated and not in the DB, ignore it
                if (!$externalPlayer->enabled && Player::query()
                                                       ->where("network_id", "=", $broadcaster->getNetworkId())
                                                       ->where("external_id", "=", $externalPlayer->external_id)
                                                       ->doesntExist()) {
                    continue;
                }

                $log->writeln("<comment>  Player #$externalPlayer->external_id - $externalPlayer->name</comment>");
                /** @var Player $player */
                $player = Player::query()->updateOrCreate([
                    "network_id"  => $broadcaster->getNetworkId(),
                    "external_id" => $externalPlayer->external_id,
                ], [
                    "location_id" => $location->id,
                    "name"        => $externalPlayer->name,
                ]);

                // Make sure the player is trashed/not-trashed according to the external player enabled status
                if ($externalPlayer->enabled && $player->trashed()) {
                    $player->restore();
                } else if (!$externalPlayer->enabled && !$player->trashed()) {
                    $player->delete();
                }

                $players[] = $player->id;
            }

            $log->writeln("<comment>Clean up players...</comment>");

            // Delete players attached to the location that weren't found
            Player::query()
                  ->where("location_id", "=", $location->getKey())
                  ->whereNotIn("id", $players)
                  ->delete();
        }

        $locationLine->overwrite("<info>Clean up locations...</info>");

        // Delete locations attached to the location that weren't found
        Location::query()->where("network_id", "=", $broadcaster->getNetworkId())
                ->whereNotIn("id", $locations)
                ->delete();

        // Update timestamp on network table
        DB::table((new Network())->getTable())
          ->where("id", "=", $this->networkId)
          ->update([
              "last_sync_at" => Carbon::now(),
          ]);

        return null;
    }

    protected function getDisplayType(BroadcasterOperator&BroadcasterLocations $broadcaster, ExternalBroadcasterResourceId $externalDisplayTypeId): DisplayType|null {
        /** @var DisplayType $displayType */
        $displayType = DisplayType::query()->firstOrNew([
            "connection_id" => $broadcaster->getBroadcasterId(),
            "external_id"   => $externalDisplayTypeId->external_id,
        ]);

        $externalDisplayType = $broadcaster->getDisplayType($externalDisplayTypeId);

        // Could not find an external display type
        if (!$externalDisplayType) {
            return null;
        }

        $displayType->internal_name = $externalDisplayType->name;
        $displayType->name          = $broadcaster->getConfig()->name . ": " . $externalDisplayType->name;

        $displayType->save();


        return $displayType;
    }

    /**
     * Replicate the container hierarchy up to the network root
     *
     * @param BroadcasterOperator&BroadcasterContainers $broadcaster
     * @param ExternalBroadcasterResourceId             $externalContainerId
     * @return int|null ID of the given external Container ID in connect, if any
     * @throws UnknownProperties
     */
    protected function persistContainersHierarchy(BroadcasterOperator&BroadcasterContainers $broadcaster, ExternalBroadcasterResourceId $externalContainerId): int|null {
        // Pull the external container
        /** @var ContainerResource|null $externalContainer */
        $externalContainer = $broadcaster->getContainer($externalContainerId);

        if (!$externalContainer) {
            return null;
        }

        // Are we at the root of the tree ?
        // If no parent is given for the container, or the container ID is the network's root, then we are at the root
        $isRoot = ($externalContainer->external_id === $broadcaster->getRootContainerId()->external_id) || !$externalContainer->parent;

        // Pull the parent container ID (in Connect)
        $parentId = $isRoot ? null : $this->persistContainersHierarchy($broadcaster, $externalContainer->parent);

        // Persist or update the container in the DB
        $container = NetworkContainer::query()->firstOrCreate([
            "network_id"  => $broadcaster->getNetworkId(),
            "parent_id"   => $parentId,
            "name"        => $externalContainer->name,
            "external_id" => $externalContainer->external_id,
        ]);

        return $container->getKey();
    }
}
