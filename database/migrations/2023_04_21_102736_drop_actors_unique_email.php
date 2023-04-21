<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_04_21_102736_drop_actors_unique_email.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('password_resets', function (Blueprint $table) {
            $table->dropForeign("password_resets_ibfk_1");
        });

        Schema::table('actors', function (Blueprint $table) {
            $table->dropIndex("actors_email_unique");
        });
    }
};
