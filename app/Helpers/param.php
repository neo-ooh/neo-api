<?php

use Neo\Enums\ParametersEnum;
use Neo\Models\Param;

if (!function_exists("param")) {
    /**
     *
     */
    function param(ParametersEnum $slug): mixed {
        /** @var Param|null $parameter */
        $parameter = Param::query()->find($slug->value);

        // If the param does not exist, we raise a warning, and return the default value
        if (!$parameter) {
            \Illuminate\Support\Facades\Log::warning("Could not found parameter $slug->value, using default value");
            return $slug->defaultValue();
        }

        switch ($parameter->format) {
            case "number":
                return (float)$parameter->value;
        }

        if (str_starts_with($parameter->format, "file:")) {
            $fileType = explode(":", $parameter->format)[1];
            return \Illuminate\Support\Facades\Storage::disk("public")->url("common/.$parameter->slug.$fileType");
        }

        return $parameter->value;
    }
}
