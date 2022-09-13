<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_28_151559_create_library_formats_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        Schema::create('library_formats', static function (Blueprint $table) {
            $table->foreignId("library_id")->constrained("libraries", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->restrictOnDelete();
        });

        $libraries = DB::table("libraries")->get();

        foreach ($libraries as $library) {
            // List contents of the library
            $formatIds = DB::table("formats")
                           ->join("format_layouts", "format_layouts.format_id", "=", "formats.id")
                           ->join("layouts", "layouts.id", "=", "format_layouts.layout_id")
                           ->join("contents", "contents.layout_id", "=", "layouts.id")
                           ->where("contents.library_id", "=", $library->id)
                           ->distinct()
                           ->pluck("formats.id")
                           ->toArray();

            foreach ($formatIds as $formatId) {
                DB::table("library_formats")->insert([
                    "library_id" => $library->id,
                    "format_id"  => $formatId,
                ]);
            }
        }

        Schema::dropColumns("libraries", "hidden_formats");
    }
};
