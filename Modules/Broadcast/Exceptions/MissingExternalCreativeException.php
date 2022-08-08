<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MissingExternalCreativeException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;

/**
 * This Exception is thrown when we try to create an external schedule, but a problem occur with the creatives
 */
class MissingExternalCreativeException extends BaseException {
    public function __construct(BroadcasterOperator $broadcaster, Creative $creative, int $code = 0) {
        parent::__construct("Creative #$creative->id is missing in Broadcaster #{$broadcaster->getBroadcasterId()}", $code);
    }
}
