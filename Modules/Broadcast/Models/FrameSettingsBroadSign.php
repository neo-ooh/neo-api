<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FrameSettingsBroadSign.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\BroadSignCriteria;

/**
 * Class FrameSettingsBroadSign
 *
 * @package Neo\Models
 *
 * @property int                $frame_id
 * @property int                $criteria_id
 *
 * @property Frame              $frame
 * @property ?BroadSignCriteria $criteria
 */
class FrameSettingsBroadSign extends Model {
    use HasFactory;

    protected $table = "frame_settings_broadsign";
    protected $primaryKey = "frame_id";
    public $incrementing = false;
    public $timestamps = false;

    public $touches = ["frame"];

    protected $fillable = [
        "frame_id"
    ];

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, "frame_id", "id");
    }

    public function criteria(): BelongsTo {
        return $this->belongsTo(BroadSignCriteria::class, "criteria_id", "id");
    }
}
