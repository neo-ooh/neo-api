<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AspectRatio.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models;

/**
 * @property int    $id
 * @property string $name
 * @property int    $horizontal
 * @property int    $vertical
 */
class AspectRatio extends ReachModel {
    public string $endpoint = "aspect_ratios";
    public string $key = "id";
}
