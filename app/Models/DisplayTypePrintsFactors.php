<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DisplayTypePrintsFactors
 *
 * @property int $id
 * @property int $display_type_id
 * @property int $network_id
 * @property int $start_month 1-indexed month
 * @property int $end_month 1-indexed month
 * @property double $product_exposure
 * @property double $exposure_length
 * @property int $loop_length
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @property Collection<DisplayType> $displayTypes
 * @property Network $network
 *
 * @package Neo\Models
 */
class DisplayTypePrintsFactors extends Model
{
    use HasFactory;

    protected $table = "display_types_prints_factors";

    protected $primaryKey = "id";

    public function displayTypes() {
        return $this->belongsToMany(DisplayType::class, "display_types_factors", "display_type_prints_factors_id", "display_type_id");
    }

    public function network() {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function getPrintsForTraffic($traffic) {
        return $traffic * ($this->product_exposure / 100.0) * $this->exposure_length;
    }
}
