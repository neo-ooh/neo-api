<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MissingRequestTargeting.php
 */

namespace Neo\Exceptions;

use Throwable;

class MissingRequestTargeting extends BaseException {
    public function __construct(Throwable $previous = null) {
        parent::__construct("Missing targeting information for requests. Make sure to provide a product, location or player ID", "screenshots.request.no-targeting", 400, $previous);
    }
}
