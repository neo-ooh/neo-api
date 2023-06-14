<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CouldNotPromoteResourceException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;

/**
 * This Exception is thrown when a promote job fails for a specific Broadcaster
 */
class CouldNotPromoteResourceException extends BaseException {
    public function __construct(BroadcasterOperator $broadcaster, int $resourceId, public array $context, int $code = 0) {
        parent::__construct("Resource #$resourceId could not be promoted on Broadcaster #{$broadcaster->getBroadcasterId()} " . json_encode($this->context), $code);
    }
}
