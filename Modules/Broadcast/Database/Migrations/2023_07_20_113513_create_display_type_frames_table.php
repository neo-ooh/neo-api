<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_20_113513_create_display_type_frames_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('display_type_frames', function (Blueprint $table) {
            $table->id();

            $table->foreignId("display_type_id")->constrained("display_types", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name", 32);
            $table->float("pos_x")->default(0);
            $table->float("pos_y")->default(0);
            $table->float("width");
            $table->float("height");

            $table->timestamps();
        });
    }
};
