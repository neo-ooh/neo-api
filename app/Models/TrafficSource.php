<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property Date $created_at
 * @property Date $update_at
 *
 * @property TrafficSourceSettingsLinkett $settings
 *
 * @package Neo\Models
 */
class TrafficSource extends Model
{
    use HasFactory;

    protected $table = "traffic_sources";

    protected $primaryKey = "id";

    protected $with = ["settings"];

    public function settings() {
        return $this->hasOne(TrafficSourceSettingsLinkett::class, "source_id", "id");
    }
}
