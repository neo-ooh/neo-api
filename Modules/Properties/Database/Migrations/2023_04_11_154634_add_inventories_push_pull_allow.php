<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_04_11_154634_add_inventories_push_pull_allow.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('inventory_providers', function (Blueprint $table) {
            $table->boolean("allow_pull")->default(true)->after("is_active");
            $table->boolean("allow_push")->default(true)->after("auto_pull");
        });
    }
};
