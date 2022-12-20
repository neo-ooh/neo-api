<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourcePerformanceData.php
 */

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class ResourcePerformanceData extends JSONDBColumn {
    public function __construct(
        public int   $network_id,

        /**
         * @var array<int>
         */
        public array $formats_id,
    ) {

    }
}
