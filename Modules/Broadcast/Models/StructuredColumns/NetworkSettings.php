<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkSettings.php
 */

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class NetworkSettings extends JSONDBColumn {
    // BroadSign customer ID to use when creating resources
    public int|null $customer_id;

    // Root container for the network resources
    public int|null $root_container_id;

    // Container where to place created reservations
    public int|null $campaigns_container_id;

    // Container where to place created creatives
    public int|null $creatives_container_id;
}
