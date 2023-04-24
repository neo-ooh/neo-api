<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Headline.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Headline
 *
 * @package Neo\Models
 * @property int                         $id
 * @property int                         $actor_id
 * @property string                      $style
 * @property Date                        $end_date
 * @property Date                        $created_at
 * @property Date                        $updated_at
 * @property Date                        $deleted_at
 *
 * @property Actor|null                  $actor
 * @property Collection<HeadlineMessage> $messages
 * @property Collection<Capability>      $capabilities
 *
 */
class Headline extends Model {
    public const STYLE_INFO = "info";
    public const STYLE_SUCCESS = "success";
    public const STYLE_WARNING = "warning";
    public const STYLE_DANGER = "danger";

    use SoftDeletes;

    protected $table = "headlines";

    protected $fillable = [
        "actor_id",
        "style",
        "end_date",
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "end_date" => "datetime",
    ];

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id");
    }

    public function messages(): HasMany {
        return $this->hasMany(HeadlineMessage::class, "headline_id", "id");
    }

    public function capabilities(): BelongsToMany {
        return $this->belongsToMany(Capability::class, "headline_capabilities", "headline_id", "capability_id");
    }

    /**
     * @return Builder
     */
    public static function query() {
        return parent::query()->orderBy("end_date", "desc");
    }
}
