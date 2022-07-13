<?php

namespace Neo\Modules\Broadcast\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class CreativeProperties extends JSONDBColumn {
    // Static creative properties
    public string|null $extension;
    public string|null $checksum;

    // Dynamic creative properties
    public string|null $url;
    public int|null $refresh_interval_minutes;
}
