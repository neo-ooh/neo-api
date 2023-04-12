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
        public BroadcasterType               $provider,
        public int                           $id,
        public ExternalBroadcasterResourceId $external_id,
        public string                        $name,
    ) {

    }
}
