<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Network.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\Models;

/**
 * @property int    $network_id
 * @property string $name
 * @property int    $owner_id
 * @property string $code
 * @property string $created_on_utc
 * @property string $updated_on_utc
 */
class Network extends HivestackModel {
    public string $key = "network_id";
}
