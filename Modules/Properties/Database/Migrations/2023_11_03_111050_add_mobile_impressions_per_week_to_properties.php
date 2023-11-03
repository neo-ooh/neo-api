<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_11_03_111050_add_mobile_impressions_per_week_to_properties.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('properties', function (Blueprint $table) {
			$table->unsignedBigInteger("mobile_impressions_per_week")->default(0)->after("cover_picture_id");
		});
	}
};
