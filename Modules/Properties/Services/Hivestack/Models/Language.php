<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Language.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\Models;

/**
 * @property string $code
 * @property int    $language_id
 * @property string $name
 */
class Language extends HivestackModel {
    public string $key = "language_id";
}
