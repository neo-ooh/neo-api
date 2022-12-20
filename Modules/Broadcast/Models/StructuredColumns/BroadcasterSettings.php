<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterSettings.php
 */

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class BroadcasterSettings extends JSONDBColumn {
    public function __construct(
        // BroadSign domain ID
        public int|null    $domain_id = null,

        // BroadSign customer to use for created resources
        public int|null    $customer_id = null,

        // PiSignage Server URL
        public string|null $server_url = null,

        // PiSignage Server auth token
        public string|null $token = null,
    ) {

    }
}
