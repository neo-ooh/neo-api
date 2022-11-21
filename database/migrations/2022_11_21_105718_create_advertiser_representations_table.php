<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_21_105718_create_advertiser_representations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertiserExternalIdsTable extends Migration {
    public function up(): void {
        Schema::create('advertiser_representations', static function (Blueprint $table) {
            $table->foreignId("advertiser_id")
                  ->constrained("advertisers", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("broadcaster_id")
                  ->constrained("broadcasters_connections")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->string("external_id", 64);

            $table->timestamps();
        });
    }
}
