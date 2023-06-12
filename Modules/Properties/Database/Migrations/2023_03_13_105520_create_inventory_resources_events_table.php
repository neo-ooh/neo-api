<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_13_105520_create_inventory_resources_events_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_resources_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId("resource_id")
                  ->constrained("inventory_resources", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("inventory_id")
                  ->constrained("inventory_providers", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->string("event_type", 32);
            $table->boolean("is_success");
            $table->json("result");

            $table->timestamp("triggered_at");
            $table->foreignId("triggered_by")
                  ->nullable()
                  ->constrained("actors", "id")
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }
};
