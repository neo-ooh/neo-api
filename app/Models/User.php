<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - User.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Actor {
    public function resolveRouteBinding($value, $field = null): ?Model {
        return $this->where("is_group", "=", false)->findOrFail($value);
    }
}
