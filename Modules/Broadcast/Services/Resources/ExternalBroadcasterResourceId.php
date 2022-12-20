<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalBroadcasterResourceId.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class ExternalBroadcasterResourceId extends ExternalBroadcasterResource {
    public function __construct(
        public int                  $broadcaster_id,
        public string               $external_id,
        public ExternalResourceType $type,
    ) {
    }
}
