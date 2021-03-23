<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandingFactory.php
 */

namespace Neo\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\Branding;

/**
 * Class BrandingFactory
 *
 * @package Neo\Models\Factories
 */
class BrandingFactory extends Factory {
    protected $model = Branding::class;

    public function definition (): array {
        return [
            "name" => $this->faker->company,
        ];
    }
}
