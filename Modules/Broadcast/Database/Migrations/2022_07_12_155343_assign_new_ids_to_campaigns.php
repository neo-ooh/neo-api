<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_12_155342_assign_new_ids_to_campaigns.php
 */

use Illuminate\Database\Migrations\Migration;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // For each of the new broadcast resources (contents, schedules, campaigns), we assign new ids in the tmp columns, and apply the same in the tables referencing them.

        $output = new ConsoleOutput();

        // Schedules
        $campaigns = \Illuminate\Support\Facades\DB::table("campaigns")->orderBy("id")->lazy(500);

        foreach ($campaigns as $campaign) {
            $output->writeln("Handling Campaign #$campaign->id");

            // Get a new ID for the resource
            $broadcastResource = BroadcastResource::query()->create([
                "type" => BroadcastResourceType::Campaign,
            ]);

            // Persist the resource ID
            DB::table("campaigns")->where("id", "=", $campaign->id)->update([
                "id_tmp" => $broadcastResource->getKey(),
            ]);

            // And pass the new ID to referencing rows in other tables
            DB::table("schedules")->where("campaign_id", "=", $campaign->id)->update([
                "campaign_id_tmp" => $broadcastResource->getKey(),
            ]);

            DB::table("campaign_shares")->where("campaign_id", "=", $campaign->id)->update([
                "campaign_id_tmp" => $broadcastResource->getKey(),
            ]);

            DB::table("campaign_locations")->where("campaign_id", "=", $campaign->id)->update([
                "campaign_id_tmp" => $broadcastResource->getKey(),
            ]);
        }
    }
};
