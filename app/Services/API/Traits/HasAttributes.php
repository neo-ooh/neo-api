<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasAttributes.php
 */

namespace Neo\Services\API\Traits;

use BackedEnum;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JsonException;
use Spatie\LaravelData\Data;

trait HasAttributes {
    protected array $attributes = [];

    /**
     * @var array<class-string<Data>>
     */
    protected array $casts = [];

    protected bool $dirty = false;


    /**
     * @param string $name
     *
     * @return mixed
     * @throws JsonException
     */
    public function &__get(string $name) {
        // Check if a method with the specified name exists
        if (method_exists($this, $name)) {
            // Yes call it and return
            $val = $this->{$name}();
            return $val;
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

    protected function castAttributeForSet(string $attr, $value) {
        if (!key_exists($attr, $this->casts)) {
            return $value;
        }

        $caster = $this->casts[$attr];

        if (is_a($value, $caster, allow_string: true)) {
            return $value;
        }

        if (is_null($value)) {
            return null;
        } else if (is_subclass_of($caster, BackedEnum::class)) {
            return $caster::from($value);
        } else if (is_subclass_of($caster, Data::class)) {
            if ($value instanceof Collection) {
                $values = $value->all();
            } else {
                $values = (array)$value;
            }

            if (array_is_list($values)) {
                return collect($value)->map(function ($v) use ($caster) {
                    return $caster::from($v);
                });
            }
            return $caster::from($value);
        }

        throw new InvalidCastException($this, $attr, $caster);
    }

    /**
     * @param string $attr
     * @param        $value
     */
    public function __set(string $attr, $value) {
        $this->attributes[$attr] = $this->castAttributeForSet($attr, $value);
        $this->dirty             = true;
    }

    /**
     * Set the given attributes on the model
     *
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes) {
        $this->attributes = [
            ...$this->attributes,
            ...Arr::map($attributes, fn($v, string $k) => $this->castAttributeForSet($k, $v)),
        ];
    }

    /**
     * Set a single attribute on the model using the given key
     *
     * @param string $attr
     * @param        $value
     * @return void
     */
    public function setAttribute(string $attr, $value): void {
        $this->attributes[$attr] = $this->castAttributeForSet($attr, $value);
    }

    /**
     * List all the set attributes of the model
     *
     * @return array
     */
    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed {
        return $this->{$key};
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
        return collect($this->getAttributes())->toArray();
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
