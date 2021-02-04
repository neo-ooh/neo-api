<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CampaignFactory.php
 */

namespace Neo\Models\Factories;

/** @var Factory $factory */

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Neo\Models\Campaign;
use Neo\Models\Format;

class CampaignFactory extends Factory {
    protected $model = Campaign::class;

    public function definition (): array
    {
        return [
            "format_id"        => Format::query()->has('layouts')->first()->id,
            "name"             => $this->faker->streetName,
            "display_duration" => $this->faker->numberBetween(10, 30),
            "content_limit"    => 15,
            "start_date"       => Date::now()->toIso8601String(),
            "end_date"         => Date::now()->addMonth()->toIso8601String(),
        ];
    }
}
