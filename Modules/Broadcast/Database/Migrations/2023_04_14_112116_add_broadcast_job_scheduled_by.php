<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_04_14_112116_add_broadcast_job_scheduled_by.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('broadcast_jobs', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->after("created_at");
            $table->foreignId("updated_by")->nullable(true)->default(null);
        });
    }
};
