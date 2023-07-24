<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatCropFrame.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * `pos_x`, `pos_y` and `scale` properties are relative values, but stored multiplied by 10_000 to prevent decimals being
 * truncated by JS when parsing API responses
 *
 * @property int              $id
 * @property int              $format_id
 * @property int              $display_type_frame_id
 * @property float            $pos_x        0 to 10_000 relative horizontal position
 * @property float            $pos_y        0 to 10_000 relative vertical position
 * @property float            $scale        0 to 10_000 relative width size of destination
 * @property float            $aspect_ratio aspect ratio of the frame
 *
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 *
 * @property DisplayTypeFrame $display_type_frame
 */
class FormatCropFrame extends Model {
    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    protected $table = "formats_crop_frames";

    protected $primaryKey = "id";

    protected $casts = [
    ];

    protected $fillable = [
        "format_id",
        "display_type_frame_id",
        "pos_x",
        "pos_y",
        "scale",
        "aspect_ratio",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function display_type_frame() {
        return $this->belongsTo(DisplayTypeFrame::class, "display_type_frame_id", "id");
    }
}
