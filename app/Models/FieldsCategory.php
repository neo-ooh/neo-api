<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldsCategory.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int               $id
 * @property string            $name_en
 * @property string            $name_fr
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 *
 * @property Collection<Field> $fields
 */
class FieldsCategory extends Model {
    protected $table = "fields_categories";

    protected $primaryKey = "id";

    protected $fillable = [
        "name_en",
        "name_fr",
    ];

    public function fields(): HasMany {
        return $this->hasMany(Field::class, "category_id", "id");
    }
}
