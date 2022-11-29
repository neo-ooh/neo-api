<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_29_154639_networks_add_slug.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NetworksAddSlug extends Migration {
    public function up(): void {
        Schema::table('networks', static function (Blueprint $table) {
            $table->string("slug", 16)
                  ->after('name');
        });
    }
}
