<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StringSetCast.php
 */

namespace Neo\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class StringSetCast implements CastsAttributes {
    public function get($model, $key, $value, $attributes) {
        return explode(",", $value);
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
