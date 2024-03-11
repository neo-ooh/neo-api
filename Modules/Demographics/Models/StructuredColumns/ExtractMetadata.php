<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExtractMetadata.php
 */

namespace Neo\Modules\Demographics\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class ExtractMetadata extends JSONDBColumn {
    public function __construct(

        // How long it took to compute the extract (Usually the SQL query execution duration)
        public int|null $executionTimeMs = null,

        // To store potential errors
        public array|null $error = null,
    ) {

    }
}
