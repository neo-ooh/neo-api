<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_28_145703_add_cities_geolocation_markets_area.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cities', function (Blueprint $table) {
            $table->point("geolocation")->nullable()->default(null);
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->polygon("area")->nullable()->default(null);
        });
    }
};
