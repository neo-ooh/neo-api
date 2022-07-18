<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_150159_create_campaign_formats_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_formats', static function (Blueprint $table) {
            $table->foreignId("campaign_id")->constrained("campaigns", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->restrictOnDelete();

            $table->unique(["campaign_id", "format_id"]);
        });
    }
};
