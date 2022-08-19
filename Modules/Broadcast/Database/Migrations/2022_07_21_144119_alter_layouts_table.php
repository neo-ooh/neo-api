<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_21_144119_alter_layouts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::rename("formats_layouts", "layouts");

        Schema::create("format_layouts", static function (Blueprint $table) {
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("layout_id")->constrained("layouts", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->boolean("is_fullscreen");
        });

        $layouts = DB::table("layouts")->get();

        foreach ($layouts as $layout) {
            DB::table("format_layouts")->insert([
                "format_id"     => $layout->format_id,
                "layout_id"     => $layout->id,
                "is_fullscreen" => $layout->is_fullscreen,
            ]);
        }

        Schema::table("layouts", static function (Blueprint $table) {
            $table->dropForeign("formats_layouts_format_id_foreign");
            $table->dropColumn("format_id");
            $table->dropColumn("is_fullscreen");
        });
    }
};
