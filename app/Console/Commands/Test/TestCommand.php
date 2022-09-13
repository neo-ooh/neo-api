<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
        Schema::table("layouts", static function (Blueprint $table) {
            $table->string("name_fr")->after("name");
        });

        $layouts = DB::table("layouts")->get();

        foreach ($layouts as $layout) {
            DB::table("layouts")->where("id", "=", $layout->id)
              ->update([
                  "name_fr" => $layout->name,
              ]);
        }

        Schema::table("layouts", static function (Blueprint $table) {
            $table->renameColumn("name", "name_en");
        });
    }
}
