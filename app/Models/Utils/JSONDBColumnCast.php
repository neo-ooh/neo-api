<?php

namespace Neo\Models\Utils;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use JsonException;

class JSONDBColumnCast implements CastsAttributes {
    /**
     * @param string $class Type of the class we are casting to
     */
    public function __construct(protected string $class) {
    }

    /**
     * @throws JsonException Thrown when decoding fails
     */
    public function get($model, string $key, $value, array $attributes) {
        // Before doing any casting, make sure the value is not null
        if (is_null($value)) {
            return null;
        }

        return new $this->class(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws InvalidArgumentException Thrown when value is not of type `$this->class`, array or null
     * @throws JsonException Thrown when encoding fails
     */
    public function set($model, string $key, $value, array $attributes) {
        // If value is null, do nothing
        if (is_null($value)) {
            return null;
        }

        // If the value is an array, we cast it to the underlying class
        if (is_array($value)) {
            $value = new $this->class($value);
        }

        // If at this step the value is not of the underlying class, we fail
        if (!($value instanceof $this->class)) {
            throw new InvalidArgumentException("\$value must be of type $this->class, array or null, " . get_class($value) . " received");
        }

        return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
    }
}
