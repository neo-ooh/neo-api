<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - APIAuthenticationError.php
 */

namespace Neo\Services\API;

use Neo\Exceptions\BaseException;

class APIAuthenticationError extends BaseException {
    public function __construct() {
        parent::__construct("Could not authenticate with API", "third-party.auth-error");
    }
}
