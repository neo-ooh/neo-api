<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_103050_schedules_table_v2_migrate_external_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Campaign;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Services\Broadcast\Broadcaster;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // For all schedules, we move its external_ids to the external_resources table
        $schedules = DB::table("schedules")->orderBy("id")->lazy(500);

        $output   = new ConsoleOutput();
        $progress = new \Symfony\Component\Console\Helper\ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start($schedules->count());

        foreach ($schedules as $schedule) {
            $progress->setMessage("Handling Schedule #$schedule->id");
            $progress->advance();

            if ($schedule->external_id_1 === null && $schedule->external_id_2 === null) {
                // Nothing to migrate
                continue;
            }

            // We need to know the broadcaster this schedule is scheduled on
            /** @var Campaign $campaign */
            $campaign = DB::table("campaigns")->where("id", "=", $schedule->campaign_id)->first();
            /** @var BroadcasterConnection|null $broadcaster */
            $broadcaster = DB::table("broadcasters_connections")
                             ->join("networks", "networks.connection_id", "=", "broadcasters_connections.id")
                             ->where("networks.id", "=", $campaign->network_id)
                             ->first(["broadcasters_connections.*"]);

            if (!$broadcaster) {
                // Broadcaster not found, ignore.
                continue;
            }

            if ($broadcaster->broadcaster === Broadcaster::BROADSIGN) {
                ExternalResource::query()->create([
                    "resource_id"    => $schedule->id,
                    "broadcaster_id" => $broadcaster->id,
                    "data"           => [
                        "type"        => ExternalResourceType::Bundle,
                        "network_id"  => $campaign->network_id,
                        "external_id" => $schedule->external_id_1,
                    ],
                ]);
            }

            ExternalResource::query()->create([
                "resource_id"    => $schedule->id,
                "broadcaster_id" => $broadcaster->id,
                "data"           => [
                    "type"        => ExternalResourceType::Schedule,
                    "network_id"  => $campaign->network_id,
                    "external_id" => $schedule->external_id_2,
                ],
            ]);
        }

        $progress->finish();
    }
};
