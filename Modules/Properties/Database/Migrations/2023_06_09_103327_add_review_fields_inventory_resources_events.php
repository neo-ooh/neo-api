<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_06_09_103327_add_review_fields_inventory_resources_events.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("inventory_resources_events", function (Blueprint $table) {
            $table->timestamp("reviewed_at")->nullable()->default(null);
            $table->foreignId("reviewed_by")
                  ->nullable()
                  ->default(null)
                  ->constrained("actors", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
};
