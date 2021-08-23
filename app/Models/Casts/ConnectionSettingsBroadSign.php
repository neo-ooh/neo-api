<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ConnectionSettingsBroadSign.php
 */

namespace Neo\Models\Casts;

use Illuminate\Support\Arr;
use Neo\Services\API\Traits\HasAttributes;

/**
 * Class ConnectionSettingsBroadSign
 *
 * @package Neo\Models
 * @property int                   $domain_id
 * @property int                   $default_customer_id
 * @property int                   $default_tracking_id
 *
 */
class ConnectionSettingsBroadSign {
    use HasAttributes;

    public string $certificate_path = "secure/certs/";
    public string $file_name;
    public string $file_path;

    public function __construct(array $attributes) {
        $this->attributes = $attributes;

        $this->file_name = Arr::get($this->attributes, "broadcaster_uuid") . ".pem";
        $this->file_path = $this->certificate_path . $this->file_name;
    }
}
