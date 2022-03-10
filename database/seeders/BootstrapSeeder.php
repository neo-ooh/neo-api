<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BootstrapSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Models\Actor;
use Neo\Models\Role;

/**
 * Class BoostrapSeeder
 *
 * @package Neo\DB\Seeders
 */
class BootstrapSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public static function run (): void {
        // Do we already have a root actor ?
        $actor = Actor::query()->where('name', '=', 'root')
                    ->where('email', '=', 'root@root.neo-ooh.info')
                    ->first();

        if (!is_null($actor)) {
            // Root actor already exist
            return;
        }

        // Create the root actor
        if (is_null(Actor::query()->where("email", "=", "root@root.neo-ooh.info")->first())) {
            $actor = new Actor();
            $actor->name = 'root';
            $actor->email = "root@root.neo-ooh.info";
            $actor->password = 'password'; // Password is in clear, but MUST be changed upon first deployment
            $actor->tos_accepted = true;
            $actor->save();
            $actor->addRoles([Role::query()->first()->id]);
        }
    }
}
