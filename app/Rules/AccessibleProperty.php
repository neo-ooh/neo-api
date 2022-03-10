<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleProperty.php
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;

/**
 * A property being tied to an actor, we can simply extend the actor accessibility rule.
 */
 class AccessibleProperty extends AccessibleActor {
    public function __construct () {
        parent::__construct(false);
    }
}
