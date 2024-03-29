<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ThirdPartyAPIException.php
 */

namespace Neo\Exceptions;

use Exception;

class ThirdPartyAPIException extends Exception {
    public function __construct(string $request = "", int $code = 0, string $response = "") {
        parent::__construct("request: $request; response: $response", $code);
    }
}
