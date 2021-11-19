<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Models\Odoo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                  $property_id
 * @property int                  $odoo_id
 * @property string               $internal_name
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property \Neo\Models\Property $property
 */
class Property extends Model {
    protected $table = "odoo_properties";

    protected $primaryKey = "property_id";

    public $incrementing = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(\Neo\Models\Property::class, "property_id", "actor_id");
    }
}
