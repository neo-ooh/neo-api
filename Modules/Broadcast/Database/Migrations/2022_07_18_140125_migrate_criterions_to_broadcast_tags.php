<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_18_140125_migrate_criterions_to_broadcast_tags.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        $output = new ConsoleOutput();

        /** @var object $broadsignProvider */
        $broadsignProvider = DB::table("broadcasters_connections")
                               ->where("broadcaster", "=", "broadsign")
                               ->first();

        // List all BroadSign triggers
        $triggers = DB::table("broadsign_triggers")->get();

        $output->writeln("Handling triggers");
        $progress = $this->getProgressBar($output);
        $progress->start($triggers->count());

        foreach ($triggers as $trigger) {
            $progress->setMessage("Handling Trigger '$trigger->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "trigger",
                "name_en" => $trigger->name,
                "name_fr" => $trigger->name,
                "scope"   => BroadcastTagScope::Layout->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $trigger->broadsign_trigger_id
                ]
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $layouts = DB::table("formats_layouts")->where("trigger_id", "=", $trigger->id)->get();

            foreach ($layouts as $layout) {
                DB::table("format_layout_broadcast_tags")->insert([
                    "format_layout_id" => $layout->id,
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();

        // List all BroadSign separations
        $separations = DB::table("broadsign_separations")->get();

        $output->writeln("Handling separations");
        $progress = $this->getProgressBar($output);
        $progress->start($separations->count());

        foreach ($separations as $separation) {
            $progress->setMessage("Handling Trigger '$separation->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "separation",
                "name_en" => $separation->name,
                "name_fr" => $separation->name,
                "scope"   => BroadcastTagScope::Layout->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $separation->broadsign_separation_id
                ]
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $layouts = DB::table("formats_layouts")->where("separation_id", "=", $separation->id)->get();

            foreach ($layouts as $layout) {
                DB::table("format_layout_broadcast_tags")->insert([
                    "format_layout_id" => $layout->id,
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();

        // List all BroadSign criteria
        $criteria = DB::table("broadsign_criteria")->get();

        $output->writeln("Handling criteria");
        $progress = $this->getProgressBar($output);
        $progress->start($separations->count());

        foreach ($criteria as $criterion) {
            $progress->setMessage("Handling Criteria '$criterion->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "criteria",
                "name_en" => $criterion->name,
                "name_fr" => $criterion->name,
                "scope"   => BroadcastTagScope::Frame->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $criterion->broadsign_criteria_id
                ]
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $frames = DB::table("frame_settings_broadsign")->where("criteria_id", "=", $criterion->id)->get();

            foreach ($frames as $frame) {
                DB::table("frame_broadcast_tags")->insert([
                    "frame_id"         => $frame->id,
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();
    }

    protected function getProgressBar(ConsoleOutput $output) {
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");

        return $progress;
    }
};
