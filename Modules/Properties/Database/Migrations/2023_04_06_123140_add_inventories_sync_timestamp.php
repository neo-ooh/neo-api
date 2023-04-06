<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_04_06_123140_add_inventories_sync_timestamp.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('inventory_providers', function (Blueprint $table) {
            $table->timestamp("last_pull_at")->after("settings")->nullable()->default(null);
            $table->timestamp("last_push_at")->after("last_pull_at")->nullable()->default(null);
        });
    }
};
