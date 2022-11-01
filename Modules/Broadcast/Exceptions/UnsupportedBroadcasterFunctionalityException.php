<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnsupportedBroadcasterFunctionalityException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Services\BroadcasterType;

class UnsupportedBroadcasterFunctionalityException extends BaseException {
    public function __construct(BroadcasterType $broadcasterType, string $functionality) {
        parent::__construct("Unsupported $broadcasterType->value functionality: $functionality", "broadcaster.unsupported-functionality");
    }
}
