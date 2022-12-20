<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_12_19_142835_display_type_resolution.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DisplayTypeResolution extends Migration {
    public function up() {
        Schema::table('display_types', function (Blueprint $table) {
            $table->unsignedInteger("width_px")->default(0)->after("internal_name");
            $table->unsignedInteger("height_px")->default(0)->after("width_px");
        });
    }
}
