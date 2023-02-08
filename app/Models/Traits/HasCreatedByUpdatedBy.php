<?php

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
                $model->{$model->createdBy} = Auth::id();
            }

            if ($model->updatedBy !== null) {
                $model->{$model->updatedBy} = Auth::id();
            }
        });

        static::updating(static function (Model $model) {
            if ($model->updatedBy !== null) {
                $model->{$model->updatedBy} = Auth::id();
            }
        });

        static::deleting(static function (Model $model) {
            if ($model->deletedBy !== null) {
                $model->{$model->deletedBy} = Auth::id();
            }
        });
    }
}
