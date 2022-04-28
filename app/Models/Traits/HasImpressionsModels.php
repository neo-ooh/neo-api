<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasImpressionsModels.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Models\ImpressionsModel;

/**
 * Defines an `impressions_models` eloquent relation for the current model.
 * Requires the `impressions_models_pivot_table` properties to be defined on the model.
 */
trait HasImpressionsModels {
    public function impressions_models(): BelongsToMany {
        return $this->belongsToMany(ImpressionsModel::class, $this->impressions_models_pivot_table, $this->getForeignKey(), "impressions_model_id");
    }
}
