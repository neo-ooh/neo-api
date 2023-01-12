<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_14_145143_parameters_capability.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Enums\Capability;

class ParametersCapability extends Migration {
    public function up(): void {
        Schema::table('parameters', static function (Blueprint $table) {
            $table->string("capability", 64)->after("format")->default(Capability::dev_tools->value);
        });
    }
}
