<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FrameSettingsPiSignage.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class FrameSettingsPiSignage
 *
 * @package Neo\Models
 *
 * @property int    $frame_id
 * @property string $zone_name
 *
 * @property Frame $frame
 */
class FrameSettingsPiSignage extends Model {
    use HasFactory;

    protected $table = "frame_settings_pisignage";
    public $primaryKey = "frame_id";
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        "frame_id"
    ];

    public $touches = ["frame"];

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, "frame_id", "id");
    }
}
