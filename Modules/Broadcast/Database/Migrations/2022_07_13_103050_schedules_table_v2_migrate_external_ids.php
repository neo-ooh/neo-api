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
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // For all schedules, we move its external_ids to the external_resources table
        $schedules = DB::table("schedules")->orderBy("id")->lazy(500);

        $output = new ConsoleOutput();
        $output->writeln("");
        $progress = new ProgressBar($output);
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
            /** @var object $campaign */
            $campaign = DB::table("campaigns")->where("id", "=", $schedule->campaign_id)->first();
            /** @var object|null $broadcaster */
            $broadcaster = DB::table("broadcasters_connections")
                             ->join("networks", "networks.connection_id", "=", "broadcasters_connections.id")
                             ->where("networks.id", "=", $campaign->network_id)
                             ->first(["broadcasters_connections.*"]);

            if (!$broadcaster) {
                // Broadcaster not found, ignore.
                continue;
            }

            if ($broadcaster->broadcaster === BroadcasterType::BroadSign->value) {
                ExternalResource::query()->create([
                    "resource_id"    => $schedule->id,
                    "broadcaster_id" => $broadcaster->id,
                    "type"           => ExternalResourceType::Bundle,
                    "data"           => new ExternalResourceData([
                        "formats_id"  => [$campaign->format_id],
                        "network_id"  => $campaign->network_id,
                        "external_id" => $schedule->external_id_1,
                    ]),
                ]);
            }

            ExternalResource::query()->create([
                "resource_id"    => $schedule->id,
                "broadcaster_id" => $broadcaster->id,
                "type"           => ExternalResourceType::Schedule,
                "data"           => new ExternalResourceData([
                    "formats_id"  => [$campaign->format_id],
                    "network_id"  => $campaign->network_id,
                    "external_id" => $schedule->external_id_2,
                ]),
            ]);
        }

        $progress->finish();
        $output->writeln("");
    }
};
