<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleContent.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int                                       $id
 * @property int                                       $schedule_id
 * @property int                                       $content_id
 * @property Carbon                                    $created_at
 * @property Carbon                                    $updated_at
 * @property Carbon|null                               $deleted_at
 *
 * @property Collection<Format>                        $disabled_formats
 * @property Collection<ScheduleContentDisabledFormat> $disabled_formats_ids
 */
class ScheduleContent extends Model {
    use AsPivot;
    use SoftDeletes;

    protected $table = "schedule_contents";

    protected $primaryKey = "id";

    protected $fillable = [
        "schedule_id",
        "content_id",
    ];

    /**
     * @return BelongsToMany<Format>
     */
    public function disabled_formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "schedule_content_disabled_formats", "schedule_content_id", "format_id");
    }

    /**
     * @return HasMany<ScheduleContentDisabledFormat>
     */
    public function disabled_formats_ids(): HasMany {
        return $this->hasMany(ScheduleContentDisabledFormat::class, "schedule_content_id", "id");
    }
}
