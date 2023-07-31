<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_25_161535_change_contracts_bursts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        /*        Schema::table('contracts_bursts', function (Blueprint $table) {
                    $table->renameColumn("start_at", "send_at");
                    $table->renameColumn("actor_id", "created_by");
                });*/

        Schema::table('screenshots_requests', function (Blueprint $table) {
            /*            $table->foreignId("product_id")
                              ->nullable()
                              ->after("id")
                              ->constrained("products", "id")
                              ->cascadeOnUpdate()
                              ->nullOnDelete();

                        $table->foreignId("player_id")
                              ->nullable()
                              ->after("location_id")
                              ->constrained("products", "id")
                              ->cascadeOnUpdate()
                              ->nullOnDelete();
*/
            $table->boolean("sent")->default(0)->after("status");

            /*            $table->foreignId("updated_by")
                              ->nullable()
                              ->after("updated_at")
                              ->constrained("actors", "id")
                              ->cascadeOnUpdate()
                              ->nullOnDelete();

                        $table->dropColumn("deleted_at");*/
        });

        /*        Schema::table('contracts_bursts', function (Blueprint $table) {
                    $table->rename("screenshots_requests");
                });

                \Illuminate\Support\Facades\DB::statement(<<<EOL
                UPDATE `screenshots_requests` SET `updated_by` = `created_by`
                EOL
                );*/

        \Illuminate\Support\Facades\DB::statement(<<<EOL
        UPDATE `screenshots_requests` SET `sent` = 1 WHERE `status` = 'ACTIVE'
        EOL
        );
    }
};
