<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_11_02_153851_migrate_networks_to_properties_networks.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Modules\Properties\Models\PropertyNetwork;

return new class extends Migration {
	public function up(): void {
		$networks = \Illuminate\Support\Facades\DB::table("networks")->get();

		foreach ($networks as $network) {
			PropertyNetwork::insert([
				                        "name"         => $network->name,
				                        "slug"         => $network->slug ?? '000',
				                        "color"        => $network->color,
				                        "ooh_sales"    => 1,
				                        "mobile_sales" => 1,
			                        ]);
		}

		Schema::table("properties", function (Blueprint $table) {
			$table->dropForeign("properties_network_id_foreign");
		});

		Schema::table("properties", function (Blueprint $table) {
			$table->foreign("network_id")->on("properties_networks")->references("id");
		});

		Schema::table("fields_networks", function (Blueprint $table) {
			$table->dropForeign("fields_networks_network_id_foreign");
		});

		Schema::table("fields_networks", function (Blueprint $table) {
			$table->foreign("network_id")->on("properties_networks")->references("id");
		});
	}
};
