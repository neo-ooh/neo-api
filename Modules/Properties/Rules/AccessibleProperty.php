<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleProperty.php
 */

namespace Neo\Modules\Properties\Rules;

use Neo\Rules\AccessibleActor;

/**
 * A property being tied to an actor, we can simply extend the actor accessibility rule.
 */
class AccessibleProperty extends AccessibleActor {
    public function __construct() {
        parent::__construct(false);
    }
}
