<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_25_165241_change_contracts_screenshots_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('contracts_screenshots', function (Blueprint $table) {
            $table->foreignId("product_id")
                  ->nullable()
                  ->after("id")
                  ->constrained("products", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->foreignId("location_id")
                  ->nullable()
                  ->after("product_id")
                  ->constrained("locations", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
            $table->foreignId("player_id")
                  ->nullable()
                  ->after("location_id")
                  ->constrained("players", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->renameColumn("burst_id", "request_id");
            $table->renameColumn("created_at", "received_at");
            $table->dropColumn("updated_at");
        });

        Schema::table('contracts_screenshots', function (Blueprint $table) {
            $table->rename("screenshots");
        });

        \Illuminate\Support\Facades\DB::statement(<<<EOL
        UPDATE `screenshots` 
          JOIN `screenshots_requests` ON `screenshots_requests`.`id` = `screenshots`.`request_id` 
          SET `screenshots`.`location_id` = `screenshots_requests`.`location_id`
        EOL
        );
    }
};
