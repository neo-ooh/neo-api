<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_11_104040_create_broadcast_resources_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

return new class extends Migration {
    public function up(): void {
        Schema::create('broadcast_resources', static function (Blueprint $table) {
            $table->id();
            $table->enum("type", array_map(static fn(BroadcastResourceType $type) => $type->value, BroadcastResourceType::cases()));
        });
    }

    public function down() {
        Schema::dropIfExists('broadcast_resources');
    }
};
