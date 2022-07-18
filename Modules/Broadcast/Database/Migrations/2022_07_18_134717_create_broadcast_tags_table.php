<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_18_134717_create_broadcast_tags_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;

return new class extends Migration {
    public function up() {
        Schema::create('broadcast_tags', function (Blueprint $table) {
            $table->foreignId("id")->primary()
                  ->constrained("broadcast_resources", "id")
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string("type", 32)->index();
            $table->string("name_en", 64);
            $table->string("name_fr", 64);
            $table->set("scope", array_map(static fn(BroadcastTagScope $tagScope) => $tagScope->value, BroadcastTagScope::cases()));

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
