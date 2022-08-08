<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeoJsonRule.php
 */

namespace Neo\Rules;

use GeoJson\GeoJson;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;
use Throwable;

/**
 *
 * @see https://github.com/yucadoo/laravel-geojson-rule/blob/master/src/GeoJsonRule.php
 *
 * RFC 7946 GeoJSON DisplayType specification.
 */
class GeoJsonRule implements Rule {
    private string|null $geometryClass;
    private Throwable $exception;

    /**
     * Constructor.
     *
     * @param string|null $geometryClass Expected geometry.
     */
    public function __construct(?string $geometryClass = null) {
        $this->geometryClass = $geometryClass;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool {
        try {
            if (is_string($value)) {
                // Handle undecoded JSON
                $value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
                if (is_null($value)) {
                    throw new InvalidArgumentException('JSON is invalid');
                }
            }
            // An exception will be thrown if parsing fails
            $geometry = GeoJson::jsonUnserialize($value);
        } catch (Throwable $t) {
            $this->exception = $t;
            return false;
        }
        // Check geometry type if specified
        if (!empty($this->geometryClass)) {
            return get_class($geometry) === $this->geometryClass;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): array|string {
        $message = 'The :attribute does not satisfy the RFC 7946 GeoJSON DisplayType specification';
        if (!empty($this->exception)) {
            $message .= ' because ' . $this->exception->getMessage();
        } elseif (!empty($this->geometryClass)) {
            $message .= ' for ' . basename(str_replace('\\', '/', $this->geometryClass));
        }
        return $message;
    }
}
