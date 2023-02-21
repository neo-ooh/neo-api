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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                                   $id
 * @property Carbon|null                           $start_date
 * @property Carbon|null                           $end_date
 * @property Carbon                                $created_at
 * @property int                                   $created_by
 * @property Carbon                                $updated_at
 * @property int                                   $updated_by
 * @property Carbon|null                           deleted_at
 * @property int|null                              deleted_by
 *
 * @property Collection<UnavailabilityTranslation> $translations
 *
 */
class Unavailability extends Model {
    use SoftDeletes;
    use HasCreatedByUpdatedBy;
    use HasPublicRelations;

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


    public function getPublicRelations(): array {
        return [
            "properties"   => "load:properties",
            "products"     => "load:products",
            "translations" => "load:translations",
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function translations(): HasMany {
        return $this->hasMany(UnavailabilityTranslation::class, "unavailability_id", "id");
    }

    /**
     * @return BelongsToMany<Property>
     */
    public function properties(): BelongsToMany {
        return $this->belongsToMany(Property::class, "properties_unavailabilities", "unavailability_id", "property_id");
    }

    /**
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, "products_unavailabilities", "unavailability_id", "product_id");
    }
}
