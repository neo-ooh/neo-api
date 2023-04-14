<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CouldNotTargetCampaignException.php
 */

namespace Neo\Modules\Broadcast\Services\Exceptions;

use Neo\Modules\Broadcast\Services\BroadcasterType;
use RuntimeException;
use Throwable;

/**
 * Thrown when the targeting of a campaign does not succeed
 */
class CouldNotTargetCampaignException extends RuntimeException {
    public function __construct(BroadcasterType $service, string $message, ?Throwable $previous = null) {
        parent::__construct("[$service->value] $message", -1, $previous);
    }
}
