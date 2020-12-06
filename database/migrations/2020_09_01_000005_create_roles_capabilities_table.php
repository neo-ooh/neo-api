<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_09_01_000005_create_roles_capabilities_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesCapabilitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void {
		Schema::create('roles_capabilities', function(Blueprint $table)
		{
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained('capabilities')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->primary(['role_id', 'capability_id'], 'roles_capability_primary');
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void {
		Schema::dropIfExists('roles_capabilities');
	}

}
