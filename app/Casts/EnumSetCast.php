<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - EnumSetCast.php
 */

namespace Neo\Casts;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class EnumSetCast implements CastsAttributes {
    /**
     * @param class-string<BackedEnum> $enum
     */
    public function __construct(protected string $enum) {
        if (!enum_exists($this->enum)) {
            throw new InvalidArgumentException("$this->enum is not a valid Enum");
        }
    }

    public function get($model, $key, $value, $attributes) {
        $e = $this->enum;
        return array_map(static fn(string $value) => $e::from($value), explode(",", $value));
    }

    public function set($model, $key, $value, $attributes) {
        if (is_array($value)) {
            return implode(",", $value);
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException("A SET field only accepts strings or array of strings");
        }

        return $value;
    }
}
