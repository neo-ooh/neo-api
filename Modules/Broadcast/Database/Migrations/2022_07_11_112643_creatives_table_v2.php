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
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Vinkla\Hashids\Facades\Hashids;

return new class extends Migration {
    public function up(): void {
        $env    = config("app.env");
        $output = (new ConsoleOutput());

        // For each creative, repatriate its settings, either from the `dynamic_creatives` or `static_creatives` table into the new `properties` JSON field
        $creatives = DB::table("creatives")->where("id_tmp", "=", 0)->orderBy("id")->get();

        $output->writeln("Iterate over every creatives...");
        $progressSection = $output->section();
        $progress        = new ProgressBar($progressSection);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start($creatives->count());

        $info = $output->section();

        foreach ($creatives as $creative) {
            $progress->setMessage("Handling Creative #$creative->id");
            $progress->advance();

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
                foreach ($externalIds as $externalId) {
                    /** @var Network $network */
                    $network = Network::query()->where("id", "=", $externalId->network_id)->first();

                    ExternalResource::query()->create([
                        "resource_id"    => $broadcastResource->getKey(),
                        "broadcaster_id" => $network->connection_id,
                        "type"           => ExternalResourceType::Creative,
                        "data"           => new ExternalResourceData([
                            "network_id"  => $externalId->network_id,
                            "external_id" => $externalId->external_id,
                        ]),
                    ]);
                }
            }

            // Rename files
            if (($env === 'production') && $creative->type === 'static') {
                // Move creative file
                $from = "creatives/" . $creative->id . "." . $creativeProperties->extension;
                $to   = "creatives/" . Hashids::encode($broadcastResource->getKey()) . "." . $creativeProperties->extension;

                $info->overwrite("<info>$from => $to</info>");
                Storage::disk("public")->move($from, $to);

                // Move creative's thumbnail
                $from = "creatives/" . $creative->id . "_thumb.jpeg";
                $to   = "creatives/" . Hashids::encode($broadcastResource->getKey()) . "_thumb.jpeg";

                Storage::disk("public")->move($from, $to);
            }
        }

        $progress->finish();
        $progressSection->clear();
        $info->clear();
    }
};
