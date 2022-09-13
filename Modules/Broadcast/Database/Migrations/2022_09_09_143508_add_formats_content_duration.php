<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_09_09_143508_add_formats_content_duration.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormatsContentDuration extends Migration {
    public function up() {
        Schema::table('formats', function (Blueprint $table) {
            $table->unsignedInteger("content_length")->default(0)->after("name");
            $table->dropColumn("is_enabled");
        });
    }
}
