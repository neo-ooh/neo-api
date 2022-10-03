<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleReviewTemplate.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Actor;
use Neo\Models\Traits\HasPublicRelations;

/**
 * NeoModels\Branding
 *
 * @property int    $id
 * @property string $text
 * @property int    $owner_id
 *
 * @property Actor  $owner
 *
 * @mixin Builder
 */
class ScheduleReviewTemplate extends Model {
    use HasPublicRelations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'review_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "owner_id",
        "text",
    ];

    protected array $publicRelations = [
        "owner" => "owner"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }
}
