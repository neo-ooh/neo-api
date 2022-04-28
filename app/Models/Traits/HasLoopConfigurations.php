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
use Neo\Models\LoopConfiguration;

/**
 * Defines a `loop_configurations` eloquent relation for the current model.
 * Requires the `loop_configurations_pivot_table` properties to be defined on the model.
 */
trait HasLoopConfigurations {
    public function loop_configurations(): BelongsToMany {
        return $this->belongsToMany(LoopConfiguration::class, $this->loop_configurations_pivot_table, $this->getForeignKey(), "loop_configuration_id");
    }
}
