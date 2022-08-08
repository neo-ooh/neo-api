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

class ExternalResourceData extends JSONDBColumn {
    public int|null $network_id;

    /**
     * @var array<int>|null
     */
    public array|null $formats_id;

    public string $external_id;
}
