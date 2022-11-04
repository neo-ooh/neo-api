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

        // Remove the now deprecated `static_creatives` and `dynamic_creatives` tables
        Schema::dropIfExists("static_creatives");
        Schema::dropIfExists("dynamic_creatives");
        Schema::dropIfExists("creatives_external_ids");

        Schema::table("creatives", static function (Blueprint $table) {
            // Make the newly created `properties_tmp` column permanent by renaming it
            $table->renameColumn("properties_tmp", "properties");
            $table->dropColumn("type_old");
        });

        Schema::table("creatives", static function (Blueprint $table) {
            // Get rid of the ID column in favor of the tmp one.
            $table->dropColumn("id");
            $table->renameColumn("id_tmp", "id");
        });

        Schema::table("creatives", static function (Blueprint $table) {
            $table->primary(["id"]);
        });

        Schema::table("creatives", static function (Blueprint $table) {
            // Add constraint on ID column
            $table->foreign("id")->references("id")
                  ->on("broadcast_resources")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

        });
    }
};
