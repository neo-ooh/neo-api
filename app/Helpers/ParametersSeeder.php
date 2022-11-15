<?php

namespace Neo\Helpers;

use Neo\Enums\ParametersEnum;
use Neo\Models\Parameter;

class ParametersSeeder {
    /**
     * @param class-string<ParametersEnum> $enum
     * @return void
     */
    public static function seed(string $enum) {
        foreach ($enum::cases() as $paramCase) {
            /** @var Parameter $param */
            $param = Parameter::query()->firstOrCreate([
                "slug" => $paramCase->value,
            ], [
                "format"     => $paramCase->format(),
                "capability" => $paramCase->capability(),
                "value"      => $paramCase->defaultValue(),
            ]);

            $param->format     = $paramCase->format();
            $param->capability = $paramCase->capability();
            $param->save();
        }
    }
}
