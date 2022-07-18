<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_11_112643_creatives_table_v2.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\StructuredColumns\CreativeProperties;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        $env    = config("app.env");
        $output = new ConsoleOutput();

        // For each creative, repatriate its settings, either from the `dynamic_creatives` or `static_creatives` table into the new `properties` JSON field
        $creatives = DB::table("creatives")->orderBy("id")->lazy(500);

        $output->writeln("Iterate over every creatives...");
        foreach ($creatives as $creative) {
            $output->writeln("handling Creative #$creative->id");

            // Get a new id for the resource
            /** @var BroadcastResource $broadcastResource */
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            // Pull properties of the creative, and build the new property object
            /** @var object $legacyProperties */
            $legacyProperties = DB::table($creative->type === 'static' ? "static_creatives" : "dynamic_creatives")
                                  ->where("creative_id", "=", $creative->id)
                                  ->first();

            $creativeProperties = new CreativeProperties();
            if ($creative->type === 'static') {
                $creativeProperties->extension = $legacyProperties->extension;
                $creativeProperties->checksum  = $legacyProperties->checksum;
            }

            if ($creative->type === 'dynamic') {
                $creativeProperties->url                      = $legacyProperties->url;
                $creativeProperties->refresh_interval_minutes = $legacyProperties->refresh_interval;
            }

            $output->writeln("Updating creative id and properties (#$creative->id -> #{$broadcastResource->getKey()})...");
            DB::table("creatives")
              ->where("id", "=", $creative->id)
              ->update([
                  "id_tmp"         => $broadcastResource->getKey(),
                  "type"           => $creative->type === 'static' ? CreativeType::Static : CreativeType::Url,
                  "properties_tmp" => $creativeProperties->toJson(),
              ]);

            // If the creative has not been deleted, we migrate its external ids

            if (is_null($creative->deleted_at)) {
                $externalIds = DB::table("creatives_external_ids")
                                 ->where("creative_id", "=", $creative->id)
                                 ->get();

                // Migrate creative's external ids
                $output->writeln("Migrating external Ids...");
                foreach ($externalIds as $externalId) {
                    /** @var Network $network */
                    $network = Network::query()->where("id", "=", $externalId->network_id)->first();

                    $output->writeln("Network #$network->id : $externalId->external_id");

                    ExternalResource::query()->create([
                        "resource_id"    => $broadcastResource->getKey(),
                        "broadcaster_id" => $network->connection_id,
                        "type"           => ExternalResourceType::Creative,
                        "data"           => [
                            "network_id"  => $externalId->network_id,
                            "external_id" => $externalId->external_id,
                        ],
                    ]);
                }
            }

            // Rename files
            if ($env === 'production' && $creative->type === 'static') {
                $output->writeln("Renaming files...");

                // Move creative file
                $from = "creatives/" . $creative->id . "." . $creativeProperties->extension;
                $to   = "creatives/creative_" . $broadcastResource->getKey() . "." . $creativeProperties->extension;

                $output->writeln("$from => $to");
                Storage::disk("public")->move($from, $to);

                // Move creative's thumbnail
                $from = "creatives/" . $creative->id . "." . $creativeProperties->extension . "_thumb.jpeg";
                $to   = "creatives/creative_" . $broadcastResource->getKey() . "_thumb.jpeg";

                $output->writeln("$from => $to");
                Storage::disk("public")->move($from, $to);
            }
        }
    }
};
