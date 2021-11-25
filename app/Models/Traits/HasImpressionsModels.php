<?php

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Models\ImpressionsModel;

/**
 * Defines an `impressions_models` eloquent relation for the current model.
 * Requires the `impressions_models_transient_table` and `impressions_models_foreign_key` properties to be defined on the model.
 */
trait HasImpressionsModels {
    public function impressions_models(): BelongsToMany {
        return $this->belongsToMany(ImpressionsModel::class, $this->impressions_models_transient_table, $this->getForeignKey(), "impressions_model_id");
    }
}
