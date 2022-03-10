<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HeadlineMessage.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class HeadlineMessage
 *
 * @package Neo\Models
 *
 * @property int id
 * @property int headline_id
 * @property string locale
 * @property string message
 * @property Date created_at
 * @property Date updated_at
 */
class HeadlineMessage extends Model
{
    use HasFactory;

    protected $table = "headlines_messages";

    protected $fillable = [
        "headline_id",
        "locale",
        "message"
    ];

    public function headline(): BelongsTo {
        return $this->belongsTo(Headline::class, "headline_id");
    }
}
