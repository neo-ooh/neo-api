<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BrandingFileFactory.php
 */

namespace Neo\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Neo\Models\BrandingFile;

/**
 * Class BrandingFileFactory
 *
 * @package Neo\Models\Factories
 */
class BrandingFileFactory extends Factory {
    protected $model = BrandingFile::class;

    public function definition (): array {
        return [
            "type"          => "logo",
            "filename"      => $this->faker->password . "." . $this->faker->fileExtension,
            "original_name" => $this->faker->password . "." . $this->faker->fileExtension,
        ];
    }
}
