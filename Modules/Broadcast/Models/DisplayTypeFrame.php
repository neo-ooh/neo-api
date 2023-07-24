<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayTypeFrame.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $display_type_id
 * @property string $name
 * @property float  $pos_x  0 to 1 relative horizontal position
 * @property float  $pos_y  0 to 1 relative vertical position
 * @property float  $width  0 to 1 relative height size
 * @property float  $height 0 to 1 relative width size
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DisplayTypeFrame extends Model {
    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    protected $table = "display_type_frames";

    protected $primaryKey = "id";

    protected $casts = [
    ];

    protected $fillable = [
        "display_type_id",
        "name",
        "pos_x",
        "pos_y",
        "width",
        "height",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function display_type() {
        return $this->belongsTo(DisplayType::class, "display_type_id", "id");
    }
}
