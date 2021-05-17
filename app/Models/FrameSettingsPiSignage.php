<?php

namespace Neo\Models;

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

    protected $table = "frames_settings_pisignage";
    public $primaryKey = "frame_id";
    public $incrementing = false;

    public $touches = ["frame"];

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, "frame_id", "id");
    }
}
