<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignModel.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Services\API\APIModel;

/**
 * Class BroadSignModel
 *
 * @package Neo\BroadSign\Models
 *
 * @extends APIModel<BroadSignClient>
 */
abstract class BroadSignModel extends APIModel {
    protected static string $unwrapKey;
    protected static string $key = "id";

    public function getBroadcasterId(): int {
        return $this->api->getConfig()->connectionID;
    }
}
