<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalResourceData.php
 */

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class ExternalResourceData extends JSONDBColumn {
    public ExternalResourceType $type;
    public int|null $network_id;

    public string $external_id;
}
