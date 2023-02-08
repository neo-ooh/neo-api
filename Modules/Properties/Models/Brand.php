<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Brand.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

/**
 * @property string                      $id
 * @property string                      $name_en
 * @property string                      $name_fr
 * @property number|null                 $parent_id
 * @property Date                        $created_at
 * @property Date                        $updated_at
 *
 * @property static|null                 $parent_brand
 * @property Collection<static>          $child_brands
 * @property Collection<Property>        $properties
 * @property Collection<PointOfInterest> $pointsOfInterests
 */
class Brand extends Model {
    protected $table = "brands";

    protected $primaryKey = "id";

    protected $fillable = ["name_en", "name_fr"];

    public function parent_brand(): BelongsTo {
        return $this->belongsTo(__CLASS__, "parent_id", "id");
    }

    /**
     * @return HasMany<static>
     */
    public function child_brands(): HasMany {
        return $this->hasMany(static::class, "parent_id", "id");
    }

    /**
     * @return BelongsToMany<Property>
     */
    public function properties(): BelongsToMany {
        return $this->belongsToMany(Property::class, "properties_tenants", "brand_id", "property_id");
    }

    /**
     * @return HasMany<PointOfInterest>
     */
    public function pointsOfInterest(): HasMany {
        return $this->hasMany(PointOfInterest::class, "brand_id", "id")->orderBy("name");
    }
}
