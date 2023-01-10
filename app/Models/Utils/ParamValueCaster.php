<?php

namespace Neo\Models\Utils;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Neo\Models\Parameter;

class ParamValueCaster implements CastsAttributes {
    /**
     * @param Parameter $model
     * @param string    $key
     * @param mixed     $value
     * @param array     $attributes
     * @return mixed|null
     */
    public function get($model, string $key, $value, array $attributes) {
        // Before doing any casting, make sure the value is not null
        if (is_null($value)) {
            return null;
        }

        return match ($model->format) {
            "number"  => (float)$value,
            "boolean" => (bool)$value,
            default   => $value,
        };
    }

    /**
     * @param Parameter $model
     * @param string    $key
     * @param           $value
     * @param array     $attributes
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes) {
        // If value is null, do nothing
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return (string)$value;
    }
}
