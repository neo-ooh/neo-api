<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_01_26_114622_broadcast_jobs_scheduled_at.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('broadcast_jobs', static function (Blueprint $table) {
            $table->timestamp("scheduled_at")->after("created_at");
        });

        DB::statement(DB::raw("UPDATE `broadcast_jobs` SET `scheduled_at` = `created_at` WHERE `id` = `id`"));
    }
};
