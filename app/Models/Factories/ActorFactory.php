<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorFactory.php
 */

namespace Neo\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Actor;

/**
 * Class ActorFactory
 *
 * @package Neo\Models\Factories
 */
class ActorFactory extends Factory {
    protected $model = Actor::class;

    public function definition (): array {
        return [
            "name"         => $this->faker->name,
            "email"        => $this->faker->unique()->safeEmail,
            "password"     => "password",
            "is_group"     => false,
            "is_locked"    => false,
            "tos_accepted" => true,
        ];
    }
}
