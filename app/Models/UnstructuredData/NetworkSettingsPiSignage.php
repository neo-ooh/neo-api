<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkSettingsPiSignage.php
 */

namespace Neo\Models\UnstructuredData;

use JsonSerializable;
use Neo\Services\API\Traits\HasAttributes;

/**
 * Class NetworkSettingsPiSignage
 *
 * @package Neo\Models
 *
 * @property int     $network_id
 */
class NetworkSettingsPiSignage implements JsonSerializable {
    use HasAttributes;

    public function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    public function jsonSerialize() {
        return $this->attributes;
    }
}
