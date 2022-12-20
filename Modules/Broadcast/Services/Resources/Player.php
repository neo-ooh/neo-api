<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Player.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class Player extends ExternalBroadcasterResourceId {
    public function __construct(
        int                                  $broadcaster_id,
        string                               $external_id,

        public bool                          $enabled,
        public string                        $name,

        public int                           $screen_count,

        public ExternalBroadcasterResourceId $location_id,
    ) {
        parent::__construct(
            broadcaster_id: $broadcaster_id,
            external_id   : $external_id,
            type          : ExternalResourceType::Player,
        );
    }
}
