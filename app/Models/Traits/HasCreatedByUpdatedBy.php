<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasCreatedByUpdatedBy.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;

/**
 * @mixin Model
 */
trait HasCreatedByUpdatedBy {
    protected string|null $createdBy = 'created_by';
    protected string|null $updatedBy = 'updated_by';
    protected string|null $deletedBy = 'deleted_by';

    public static function bootHasCreatedByUpdatedBy() {
        static::creating(static function (Model $model) {
            if ($model->createdBy !== null) {
                $model->{$model->createdBy} = Auth::id() ?? 1;
            }

            if ($model->updatedBy !== null) {
                $model->{$model->updatedBy} = Auth::id() ?? 1;
            }
        });

        static::updating(static function (Model $model) {
            if ($model->updatedBy !== null) {
                $model->{$model->updatedBy} = Auth::id() ?? 1;
            }
        });

        static::deleting(static function (Model $model) {
            if ($model->deletedBy !== null) {
                $model->{$model->deletedBy} = Auth::id() ?? 1;
            }
        });
    }

    /**
     * @return BelongsTo<Actor>
     */
    public function created_by(): BelongsTo {
        return $this->belongsTo(Actor::class, "created_by", "id");
    }

    /**
     * @return BelongsTo<Actor>
     */
    public function updated_by(): BelongsTo {
        return $this->belongsTo(Actor::class, "updated_by", "id");
    }

    /**
     * @return BelongsTo<Actor>
     */
    public function deleted_by(): BelongsTo {
        return $this->belongsTo(Actor::class, "deleted_by", "id");
    }
}
