<?php

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class CreativeProperties extends JSONDBColumn {
    public function __construct(
        public string|null $mime = null,

        // Static creative properties
        public string|null $extension = null,
        public string|null $checksum = null,

        // Dynamic creative properties
        public string|null $url = null,
        public int|null    $refresh_interval_minutes = null,
    ) {
    }
}
