<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @package Neo\Models
 * @property string                       $type
 * @property string                       $name
 * @property Date                         $created_at
 * @property Date                         $update_at
 *
 * @property TrafficSourceSettingsLinkett $settings
 *
 * @property int                          $id
 */
class TrafficSource extends Model {
    use HasFactory;

    protected $table = "traffic_sources";

    protected $primaryKey = "id";

    public function settings(): HasOne {
        return $this->hasOne(TrafficSourceSettingsLinkett::class, "source_id", "id");
    }
}
