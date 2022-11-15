<?php

namespace Neo\Modules\Broadcast\Jobs\Performances;

use Neo\Modules\Broadcast\Models\ExternalResource;

class PerformanceBatch {
    /**
     * @param int                $broadcaster_id
     * @param int                $network_id
     * @param ExternalResource[] $external_resources
     */
    public function __construct(
        public int   $broadcaster_id,
        public int   $network_id,
        public array $external_resources = [],
    ) {
    }
}
