<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_29_164913_cities_null_market_on_delete.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign("cities_market_id_foreign");
            $table->foreign("market_id")
                  ->references("id")->on("markets")
                  ->cascadeOnUpdate()
                  ->nullOnDelete()
                  ->change();
        });
    }
};
