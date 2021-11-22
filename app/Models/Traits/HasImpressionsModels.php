<?php

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Defines an `impressions_models` eloquent relation for the current model.
 * Requires the `impressions_models_transient_table` and `impressions_models_foreign_key` properties to be defined on the model.
 */
trait HasImpressionsModels {
    public function impressions_models(): BelongsToMany {
        return $this->belongsToMany(static::class, $this->impressions_models_transient_table, "impressions_model_id", "impressions_model_id",
            $this->getForeignKey());
    }
}
