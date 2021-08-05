<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Country
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 *
 * @property Collection<Province> $provinces
 *
 * @package Neo\Models
 */
class Country extends Model
{
    use HasFactory;

    protected $table = "countries";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = ["name", "slug"];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function provinces() {
        return $this->hasMany(Province::class, "country_id");
    }
}
