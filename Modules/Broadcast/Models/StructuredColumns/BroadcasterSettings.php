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
use Spatie\DataTransferObject\Attributes\MapFrom;

class BroadcasterSettings extends JSONDBColumn {
    // BroadSign domain ID
    public int|null $domain_id;

    // BroadSign customer to use for created resources
    #[MapFrom("default_customer_id")]
    public int|null $customer_id;

    // PiSignage Server URL
    public string|null $server_url;

    // PiSignage Server auth token
    public string|null $token;
}
