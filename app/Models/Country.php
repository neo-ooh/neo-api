<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Country.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Country
 *
 * @package Neo\Models
 * @property string               $slug
 * @property string               $name
 *
 * @property Collection<Province> $provinces
 *
 * @property int                  $id
 */
class Country extends Model {
    protected $table = "countries";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = ["name", "slug"];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string {
        return 'slug';
    }

    public function provinces(): HasMany {
        return $this->hasMany(Province::class, "country_id");
    }
}
