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
<<<<<<< HEAD
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        $output = new ConsoleOutput();
        $output->writeln("");
=======
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

function getProgressBar(ConsoleOutput $output) {
    $progress = new ProgressBar($output);
    $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
    $progress->setMessage("");

    return $progress;
}

return new class extends Migration {
    public function up() {
        $output = new ConsoleOutput();
>>>>>>> 6550a640 (Add tags migrations)

        /** @var object $broadsignProvider */
        $broadsignProvider = DB::table("broadcasters_connections")
                               ->where("broadcaster", "=", "broadsign")
                               ->first();

        // List all BroadSign triggers
        $triggers = DB::table("broadsign_triggers")->get();

        $output->writeln("Handling triggers");
<<<<<<< HEAD
        $progress = $this->getProgressBar($output);
=======
        $progress = getProgressBar($output);
>>>>>>> 6550a640 (Add tags migrations)
        $progress->start($triggers->count());

        foreach ($triggers as $trigger) {
            $progress->setMessage("Handling Trigger '$trigger->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
<<<<<<< HEAD
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Tag]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => BroadcastTagType::Trigger,
=======
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "trigger",
>>>>>>> 6550a640 (Add tags migrations)
                "name_en" => $trigger->name,
                "name_fr" => $trigger->name,
                "scope"   => BroadcastTagScope::Layout->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
<<<<<<< HEAD
                "resource_id"    => $broadcastResource->getKey(),
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => new ExternalResourceData([
                    "external_id" => $trigger->broadsign_trigger_id
                ])
=======
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $trigger->broadsign_trigger_id
                ]
>>>>>>> 6550a640 (Add tags migrations)
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $layouts = DB::table("formats_layouts")->where("trigger_id", "=", $trigger->id)->get();

            foreach ($layouts as $layout) {
<<<<<<< HEAD
                DB::table("layout_broadcast_tags")->insert([
                    "layout_id"        => $layout->id,
=======
                DB::table("format_layout_broadcast_tags")->insert([
                    "format_layout_id" => $layout->id,
>>>>>>> 6550a640 (Add tags migrations)
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();

        // List all BroadSign separations
        $separations = DB::table("broadsign_separations")->get();

        $output->writeln("Handling separations");
<<<<<<< HEAD
        $progress = $this->getProgressBar($output);
=======
        $progress = getProgressBar($output);
>>>>>>> 6550a640 (Add tags migrations)
        $progress->start($separations->count());

        foreach ($separations as $separation) {
            $progress->setMessage("Handling Trigger '$separation->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
<<<<<<< HEAD
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Tag]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => BroadcastTagType::Category,
=======
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "separation",
>>>>>>> 6550a640 (Add tags migrations)
                "name_en" => $separation->name,
                "name_fr" => $separation->name,
                "scope"   => BroadcastTagScope::Layout->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
<<<<<<< HEAD
                "resource_id"    => $broadcastResource->getKey(),
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => new ExternalResourceData([
                    "external_id" => $separation->broadsign_separation_id
                ])
=======
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $separation->broadsign_separation_id
                ]
>>>>>>> 6550a640 (Add tags migrations)
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $layouts = DB::table("formats_layouts")->where("separation_id", "=", $separation->id)->get();

            foreach ($layouts as $layout) {
<<<<<<< HEAD
                DB::table("layout_broadcast_tags")->insert([
                    "layout_id"        => $layout->id,
=======
                DB::table("format_layout_broadcast_tags")->insert([
                    "format_layout_id" => $layout->id,
>>>>>>> 6550a640 (Add tags migrations)
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();

        // List all BroadSign criteria
        $criteria = DB::table("broadsign_criteria")->get();

        $output->writeln("Handling criteria");
<<<<<<< HEAD
        $progress = $this->getProgressBar($output);
=======
        $progress = getProgressBar($output);
>>>>>>> 6550a640 (Add tags migrations)
        $progress->start($separations->count());

        foreach ($criteria as $criterion) {
            $progress->setMessage("Handling Criteria '$criterion->name");
            $progress->advance();

            // Insert the trigger in the tags table
            /** @var BroadcastResource $broadcastResource */
<<<<<<< HEAD
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Tag]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => BroadcastTagType::Targeting,
=======
            $broadcastResource = BroadcastResource::query()->create(["type" => BroadcastResourceType::Creative]);

            DB::table("broadcast_tags")->insert([
                "id"      => $broadcastResource->getKey(),
                "type"    => "criteria",
>>>>>>> 6550a640 (Add tags migrations)
                "name_en" => $criterion->name,
                "name_fr" => $criterion->name,
                "scope"   => BroadcastTagScope::Frame->value,
            ]);

            // Insert the external_id
            ExternalResource::query()->create([
<<<<<<< HEAD
                "resource_id"    => $broadcastResource->getKey(),
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => new ExternalResourceData([
                    "external_id" => $criterion->broadsign_criteria_id
                ])
=======
                "resource_id"    => $broadcastResource,
                "broadcaster_id" => $broadsignProvider->id,
                "type"           => ExternalResourceType::Tag,
                "data"           => [
                    "external_id" => $criterion->broadsign_criteria_id
                ]
>>>>>>> 6550a640 (Add tags migrations)
            ]);

            // List all the layout using the trigger, and add a reference to the newly created tag
            $frames = DB::table("frame_settings_broadsign")->where("criteria_id", "=", $criterion->id)->get();

            foreach ($frames as $frame) {
                DB::table("frame_broadcast_tags")->insert([
<<<<<<< HEAD
                    "frame_id"         => $frame->frame_id,
=======
                    "frame_id"         => $frame->id,
>>>>>>> 6550a640 (Add tags migrations)
                    "broadcast_tag_id" => $broadcastResource->getKey(),
                ]);
            }
        }

        $progress->finish();
    }
<<<<<<< HEAD

    protected function getProgressBar(ConsoleOutput $output) {
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");

        return $progress;
    }
=======
>>>>>>> 6550a640 (Add tags migrations)
};
