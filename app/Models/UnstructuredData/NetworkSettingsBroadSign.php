<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkSettingsBroadSign.php
 */

namespace Neo\Models\UnstructuredData;

use JsonSerializable;
use Neo\Services\API\Traits\HasAttributes;

/**
 * Class NetworkSettingsBroadSign
 *
 * @package Neo\Models
 * @property int $network_id
 * @property int $customer_id
 * @property int $container_id
 * @property int $tracking_id
 * @property int $reservations_container_id
 * @property int $ad_copies_container_id
 */
class NetworkSettingsBroadSign implements JsonSerializable {
    use HasAttributes;

    public function __construct(array $attributes) {
        $this->attributes = $attributes;
    }

    public function jsonSerialize(): mixed {
        return $this->attributes;
    }
}
