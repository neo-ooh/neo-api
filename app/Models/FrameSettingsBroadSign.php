<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $table = "frames_settings_broadsign";
    protected $primaryKey = "frame_id";
    public $incrementing = false;

    public $touches = ["frame"];

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, "frame_id", "id");
    }

    public function criteria(): BelongsTo {
        return $this->belongsTo(BroadSignCriteria::class, "criteria_id", "id");
    }
}
