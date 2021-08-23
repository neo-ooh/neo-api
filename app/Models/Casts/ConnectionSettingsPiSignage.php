<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ConnectionSettingsPiSignage.php
 */

namespace Neo\Models\Casts;

use JsonSerializable;
use Neo\Services\API\Traits\HasAttributes;

/**
 * Class ConnectionSettingsPiSignage
 *
 * @package Neo\Models
 * @property string                $server_url
 * @property string                $token
 */
class ConnectionSettingsPiSignage implements JsonSerializable {
    use HasAttributes;

    public function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    public function jsonSerialize() {
        return $this->attributes;
    }
}
