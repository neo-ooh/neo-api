<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_12_155341_assign_new_ids_to_contents.php
 */

use Illuminate\Database\Migrations\Migration;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Models\BroadcastResource;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        // For each of the new broadcast resources (contents, schedules, campaigns), we assign new ids in the tmp columns, and apply the same in the tables referencing them.

        // Contents
        $contents = \Illuminate\Support\Facades\DB::table("contents")->orderBy("id")->lazy(500);

        $output = new ConsoleOutput();
        $output->writeln("");
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start($contents->count());

        foreach ($contents as $content) {
            $progress->setMessage("Handling Content #$content->id");
            $progress->advance();

            // Get a new ID for the resource
            $broadcastResource = BroadcastResource::query()->create([
                "type" => BroadcastResourceType::Content,
            ]);

            // Persist the resource ID
            DB::table("contents")->where("id", "=", $content->id)->update([
                "id_tmp" => $broadcastResource->getKey(),
            ]);

            // And pass the new ID to referencing rows in other tables
            DB::table("creatives")->where("content_id", "=", $content->id)->update([
                "content_id_tmp" => $broadcastResource->getKey(),
            ]);

            DB::table("schedules")->where("content_id", "=", $content->id)->update([
                "content_id_tmp" => $broadcastResource->getKey(),
            ]);
        }

        $progress->finish();
        $output->writeln("");
    }
};
