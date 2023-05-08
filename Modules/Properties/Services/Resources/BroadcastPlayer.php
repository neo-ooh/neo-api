<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastPlayer.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

class BroadcastPlayer extends InventoryResource {
    public function __construct(
        /**
         * @var int Connect ID of the player
         */
        public int                           $id,

        /**
         * @var ExternalBroadcasterResourceId Id of the player inside its broadcaster
         */
        public ExternalBroadcasterResourceId $external_id,

        /**
         * @var string Name of the player
         */
        public string                        $name,

        /**
         * @var int Number of screens on the player
         */
        public int                           $screen_count,
    ) {

    }
}
