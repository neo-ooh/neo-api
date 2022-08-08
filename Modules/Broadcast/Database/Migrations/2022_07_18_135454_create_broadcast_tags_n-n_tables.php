<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_18_135454_create_broadcast_tags_n-n_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Formats
        Schema::create('format_broadcast_tags', function (Blueprint $table) {
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("broadcast_tag_id")->constrained("broadcast_tags", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Layouts
        Schema::create('layout_broadcast_tags', function (Blueprint $table) {
            $table->foreignId("layout_id")->constrained("formats_layouts", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("broadcast_tag_id")->constrained("broadcast_tags", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Frames
        Schema::create('frame_broadcast_tags', function (Blueprint $table) {
            $table->foreignId("frame_id")->constrained("frames", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("broadcast_tag_id")->constrained("broadcast_tags", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Campaigns, Schedules, Contents, Creatives
        Schema::create('broadcast_resource_tags', function (Blueprint $table) {
            $table->foreignId("resource_id")->constrained("broadcast_resources", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("broadcast_tag_id")->constrained("broadcast_tags", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
