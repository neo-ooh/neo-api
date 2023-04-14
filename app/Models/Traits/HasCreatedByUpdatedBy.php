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
    public function getCreatedByColumn(): string|null {
        return "created_by";
    }

    public function getUpdatedByColumn(): string|null {
        return "updated_by";
    }

    public function getDeletedByColumn(): string|null {
        return "deleted_by";
    }

    public static function bootHasCreatedByUpdatedBy() {
        static::creating(static function (Model $model) {
            $createdBy = $model->getCreatedByColumn();
            if ($createdBy !== null) {
                $model->{$createdBy} = Auth::id();
            }

            $updatedBy = $model->getUpdatedByColumn();
            if ($updatedBy !== null) {
                $model->{$updatedBy} = Auth::id();
            }
        });

        static::updating(static function (Model $model) {
            $updatedBy = $model->getUpdatedByColumn();
            if ($updatedBy !== null) {
                $model->{$updatedBy} = Auth::id();
            }
        });

        static::deleting(static function (Model $model) {
            $deletedBy = $model->getDeletedByColumn();
            if ($deletedBy !== null) {
                $model->{$deletedBy} = Auth::id();
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
