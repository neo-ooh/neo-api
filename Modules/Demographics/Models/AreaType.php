<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AreaType.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                   $id
 * @property string                $code
 *
 * @property-read Collection<Area> $areas
 */
class AreaType extends Model {
    use HasPublicRelations;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * The database of the model's table.
     *
     * @var string
     */
    protected $connection = "neo_demographics";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "areas_types";

    public $timestamps = false;

    protected $fillable = [
        "code",
    ];

    protected function getPublicRelations() {
        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function areas() {
        return $this->hasMany(Area::class, "type_id", "id");
    }
}
