<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatLayoutPivot.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int  $format_id
 * @property int  $layout_id
 * @property bool $is_fullscreen
 */
class FormatLayoutPivot extends Pivot {
    protected $table = "format_layouts";

    protected $casts = [
        "is_fullscreen" => "bool"
    ];
}
