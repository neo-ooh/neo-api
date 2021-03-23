<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibraryFactory.php
 */

namespace Neo\Models\Factories;

/** @var Factory $factory */

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Library;

class LibraryFactory extends Factory {
    protected $model = Library::class;

    public function definition (): array
    {
        return [
            "name"          => $this->faker->slug(3),
            "content_limit" => $this->faker->numberBetween(5, 10),
        ];
    }
}
