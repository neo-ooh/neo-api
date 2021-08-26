<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasAttributes.php
 */

namespace Neo\Services\API\Traits;

use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Log;
use JsonException;

trait HasAttributes {
    protected array $attributes;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function &__get(string $name) {
        // Check if a method with the specified name exists
        if (method_exists($this, $name)) {
            // Yes call it and return
            $relation = $this->{$name}();
            return $relation;
        }

        // Return the attribute with the provided name
        if (!isset($this->attributes[$name]) && method_exists($this, 'handleMissingAttribute')) {
            $this->handleMissingAttribute($name);
        }

        return $this->attributes[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function __set(string $name, $value) {
        $this->attributes[$name] = $value;
        $this->dirty             = true;
    }

    /**
     */
    public function __toString(): string {
        try {
            /** @var string $serialized */
            $serialized = json_encode($this->attributes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (JsonException $e) {
            return $e->getMessage();
        }

        return $serialized;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return $this->attributes;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     *
     * @return string
     *
     * @throws JsonException
     */
    public function toJson(int $options = 0): string {
        return json_encode($this->attributes, JSON_THROW_ON_ERROR | $options);
    }
}
