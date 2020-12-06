<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CreativeFactory.php
 */

namespace Neo\Models\Factories;

/** @var Factory $factory */

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Creative;

class CreativeFactory extends Factory {
    protected $model = Creative::class;

    public function definition (): array
    {
        return [
            "extension" => "jpg",
            "status"    => "OK",
            "checksum"  => $this->faker->slug(3),
        ];
    }
}
