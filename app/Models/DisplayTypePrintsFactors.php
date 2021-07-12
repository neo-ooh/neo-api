<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DisplayTypePrintsFactors
 *
 * @property int $id
 * @property int $display_type_id
 * @property int $network_id
 * @property int $start_month
 * @property int $end_month
 * @property double $product_exposure
 * @property double $exposure_length
 * @property int $loop_length
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @property DisplayType $displayType
 * @property Network $network
 *
 * @package Neo\Models
 */
class DisplayTypePrintsFactors extends Model
{
    use HasFactory;

    protected $table = "display_types_prints_factors";

    protected $primaryKey = "id";

    public function displayType() {
        return $this->belongsTo(DisplayType::class, "display_type_id");
    }

    public function network() {
        return $this->belongsTo(Network::class, "network_id");
    }
}
