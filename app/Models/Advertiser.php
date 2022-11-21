<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Advertiser.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int    $id
 * @property string $name
 * @property int    $odoo_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Advertiser extends Model {
    use HasPublicRelations;

    protected $table = "advertisers";

    protected $primaryKey = "id";

    protected $fillable = [
        "name",
        "odoo_id",
    ];

    protected array $publicRelations = [
        "representations" => "representations",
    ];

    /**
     * @return HasMany<AdvertiserRepresentation>
     */
    public function representations(): HasMany {
        return $this->hasMany(AdvertiserRepresentation::class, "advertiser_id", "id");
    }
}
