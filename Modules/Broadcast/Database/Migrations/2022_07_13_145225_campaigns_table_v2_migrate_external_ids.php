<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_145225_campaigns_table_v2_migrate_external_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Neo\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // For each campaign, we insert its external ID in the `external_resources` table
        $campaigns = \Illuminate\Support\Facades\DB::table("campaigns")->orderBy("id")->lazy(500);

        $output   = new ConsoleOutput();
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start($campaigns->count());

        foreach ($campaigns as $campaign) {
            $progress->setMessage("Handling Campaign #$campaign->id");
            $progress->advance();

            if ($campaign->external_id === null || $campaign->network_id === null) {
                // Ignore if campaign has no external id or network associated
                continue;
            }

            /** @var BroadcasterConnection $broadcaster */
            $broadcaster = DB::table("broadcasters_connections")
                             ->join("networks", "networks.connection_id", "=", "broadcasters_connections.id")
                             ->where("networks.id", "=", $campaign->network_id)
                             ->first(["broadcasters_connections.*"]);

            if (!$broadcaster) {
                // Broadcaster not found, ignore.
                continue;
            }

            ExternalResource::query()->create([
                "resource_id"    => $campaign->id,
                "broadcaster_id" => $broadcaster->id,
                "data"           => [
                    "type"        => ExternalResourceType::Campaign,
                    "network_id"  => $campaign->network_id,
                    "external_id" => $campaign->external_id
                ]
            ]);
        }

        $progress->finish();
        $output->writeln("");
    }
};
