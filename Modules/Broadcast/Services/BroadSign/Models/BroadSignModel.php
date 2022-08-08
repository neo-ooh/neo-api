<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignModel.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Neo\Services\API\APIModel;

/**
 * Class BroadSignModel
 *
 * @package Neo\BroadSign\Models
 *
 * @extends APIModel<\Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient>
 */
abstract class BroadSignModel extends APIModel {
    protected static string $unwrapKey;
    protected static string $key = "id";
}