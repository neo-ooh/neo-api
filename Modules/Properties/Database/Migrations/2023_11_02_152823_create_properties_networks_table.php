<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_11_02_152823_create_properties_networks_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('properties_networks', function (Blueprint $table) {
			$table->id();

			$table->string("name", 64);
			$table->string("color", 7);
			$table->string("slug", 6);
			$table->boolean("ooh_sales");
			$table->boolean("mobile_sales");

			$table->timestamps();
			$table->softDeletes();
		});
	}
};
