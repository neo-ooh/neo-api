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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        $output = new ConsoleOutput();
        $output->writeln("");
        $output->writeln("Drop auxiliary tables foreign keys");

        // Update the table columns
        $output->writeln("Update creative table with new columns...");
        Schema::table('creatives', static function (Blueprint $table) {
            // Remove unused column
            $table->dropColumn("status");

            // Add new column to hold the new ids
            $table->foreignId("id_tmp")->after("id");

            // Add new column to store creative properties; instead of having additional tables
            $table->json("properties_tmp")->after("duration");

            $table->renameColumn("type", "type_old");
        });

        Schema::table('creatives', static function (Blueprint $table) {
            $table->enum("type", ["static", "url"])->after("frame_id");
        });
    }
};
