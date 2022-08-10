<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_150851_alter_campaign_locations_table.php
 */

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        $output = new ConsoleOutput();
        $output->writeln("");

        $output->writeln("Updating campaign_locations table...");
        Schema::table('campaign_locations', static function (Blueprint $table) {
            $table->dropColumn(["broadsign_reservation_id"]);
            $table->foreignId("format_id")->after("location_id");
        });

        // We now need to go over every location of every campaign to attach the proper format
        // In the previous system, campaigns could only have only one format, so this makes it easier
        $campaigns = DB::table("campaigns")->orderBy("id")->lazy(500);

        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start($campaigns->count());

        foreach ($campaigns as $campaign) {
            $progress->setMessage("Handling Campaign #$campaign->id");
            $progress->advance();

            // List the locations of the campaign
            DB::table("campaign_locations")
              ->where("campaign_id", "=", $campaign->id)
              ->update([
                  "format_id"  => $campaign->format_id,
                  "created_at" => Carbon::now(),
                  "updated_at" => Carbon::now(),
              ]);
        }

        Schema::table('campaign_locations', static function (Blueprint $table) {
            $table->foreign("format_id")->references("id")->on("formats")->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
