<?php

namespace Neo\Helpers;

use Neo\Enums\ParametersEnum;
use Neo\Models\Param;

class ParametersSeeder {
    /**
     * @param class-string<ParametersEnum> $enum
     * @return void
     */
    public static function seed(string $enum) {
        foreach ($enum::cases() as $paramCase) {
            /** @var Param $param */
            $param = Param::query()->firstOrCreate([
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
