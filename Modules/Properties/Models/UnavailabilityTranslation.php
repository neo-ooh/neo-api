<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnavailabilityTranslation.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $unavailability_id
 * @property string $locale fr-CA / en-CA - varchar(5)
 * @property string $reason varchar(255)
 * @property string $comment text
 */
class UnavailabilityTranslation extends Model {
    protected $table = "unavailabilities_translations";
    protected $primaryKey = null;
    protected $fillable = [
        "unavailability_id",
        "locale",
        "reason",
        "comment",
    ];
}
