<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - RoleFactory.php
 */

namespace Neo\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Role;


class RoleFactory extends Factory {
    protected $model = Role::class;

    /**
     * @return array
     */
    public function definition (): array {
        return [
            "name" => substr($this->faker->jobTitle, 64),
            "desc" => $this->faker->jobTitle . '---desc',
        ];
    }
}
