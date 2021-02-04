<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ContentFactory.php
 */

namespace Neo\Models\Factories;

/** @var Factory $factory */

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Content;
use Neo\Models\Format;

class ContentFactory extends Factory {
    protected $model = Content::class;

    public function definition (): array
    {
        return [
            "layout_id"           => Format::query()->has('layouts')->first()->layouts[0]->id,
            "name"                => $this->faker->name,
            "scheduling_duration" => 0,
            "scheduling_times"    => 0,
        ];
    }
}
