<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Unavailability.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;

/**
 * @property int                                   $id
 * @property Carbon|null                           $start_date
 * @property Carbon|null                           $end_date
 * @property Carbon                                $created_at
 * @property int                                   $created_by
 * @property Carbon                                $updated_at
 * @property int                                   $updated_by
 * @property Carbon                                deleted_at
 * @property int                                   deleted_by
 *
 * @property Collection<UnavailabilityTranslation> $translations
 *
 */
class Unavailability extends Model {
    use SoftDeletes;
    use HasCreatedByUpdatedBy;

    protected $table = "unavailabilities";

    protected $primaryKey = "id";

    protected $casts = [
        "start_date" => "date",
        "end_date"   => "date",
    ];

    protected $fillable = [
        "start_date",
        "end_date",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function translations(): HasMany {
        return $this->hasMany(PropertyTranslation::class, "property_id", "id");
    }
}
