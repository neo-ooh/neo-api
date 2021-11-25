<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductType.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $name_en
 * @property string $name_fr
 * @property int    $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ProductType extends Model {
    protected $table = "products_types";

    protected $fillable = [
        "name_en",
        "name_fr",
        "external_id"
    ];

    public function categories(): HasMany {
        return $this->hasMany(ProductCategory::class, "type_id", "id");
    }
}
