<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs;


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
use Neo\Services\Broadcast\PiSignage\Models\Group;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class SynchronizeLocations extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $parsedLocations = [];

    public function uniqueId(): int {
        return $this->config->networkID;
    }

    public function handle(): void {
        (new ConsoleOutput())->writeLn("Synchronizing network {$this->config->networkUUID}...\n\n");

        // PiSignage groups/locations don't have any assigned display type. We will just make sure there is a default display type for this connection's locations.

        $displayType = DisplayType::query()->firstOrCreate([
            "connection_id" => $this->config->connectionID,
            "external_id" => 0
        ], [
            "name" => "default",
            "internal_name" => "default",
        ]);

        // Now, all we need is to map all groups on the PiSignage server to locations on Connect
        $groups = Group::all($this->getAPIClient());

        if($groups->count() === 0) {
            return;
        }

        $progressBar = $this->makeProgressBar(count($groups));
        $progressBar->start();

        /** @var Group $group */
        foreach ($groups as $group) {
            /** @var Location $location */
            $location = Location::query()->firstOrCreate([
                "network_id" => $this->config->networkID,
                "external_id" => $group->_id,
            ], [
                "name"          => $group->name,
                "internal_name" => $group->name,
                "province"        => "-",
                "city"        => "-",
            ]);

            $location->name = $group->name;
            $location->internal_name = $group->name;
            $location->display_type_id = $displayType->id;
            $location->save();

            $this->parsedLocations[] = $location->id;

            /** @noinspection DisconnectedForeachInstructionInspection */
            $progressBar->advance();
        }

        $progressBar->setMessage("Done.\n");
        $progressBar->finish();

        // All groups are now replicated in Connect, all what's left is to remove unmatched location in the network
        Location::query()->where("network_id", "=", $this->config->networkID)->whereNotIn("id", $this->parsedLocations)->delete();
    }

}
