<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastLocation.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Broadcast\Services\BroadcasterType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

class BroadcastLocation extends InventoryResource {
    public function __construct(
        /**
         * Broadcaster type of the location
         */
        public BroadcasterType               $provider,

        /**
         * @var int Connect ID of the location
         */
        public int                           $id,

        /**
         * @var ExternalBroadcasterResourceId Id of the location inside its broadcaster
         */
        public ExternalBroadcasterResourceId $external_id,

        /**
         * @var string Name of the location
         */
        public string                        $name,

        /**
         * @var int Number of screens of the location
         */
        public int                           $screen_count,
    ) {

    }
}
