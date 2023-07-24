<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_20_113951_create_formats_crop_frames.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('formats_crop_frames', function (Blueprint $table) {
            $table->id();
            $table->foreignId("format_id")
                  ->constrained("formats", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("display_type_frame_id")
                  ->constrained("display_type_frames", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->float("pos_x")->default(0);
            $table->float("pos_y")->default(0);
            $table->float("scale")->default(1);
            $table->float("aspect_ratio")->default(1);

            $table->timestamps();
        });
    }
};
