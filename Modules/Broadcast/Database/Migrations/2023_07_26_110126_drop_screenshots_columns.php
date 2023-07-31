<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_26_110126_drop_screenshots_columns.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('screenshots_requests', function (Blueprint $table) {
            $table->dropColumn("status");
            $table->dropForeign("contracts_bursts_contract_id_foreign");
            $table->dropColumn("contract_id");
        });

        Schema::table('screenshots', function (Blueprint $table) {
            $table->dropColumn("is_locked");
        });
    }
};
