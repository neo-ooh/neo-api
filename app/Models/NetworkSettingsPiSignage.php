<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NetworkSettingsPiSignage
 *
 * @package Neo\Models
 *
 * @property int     $network_id
 * @property Network $network
 *
 */
class NetworkSettingsPiSignage extends Model {
    use HasFactory;

    protected $table = "network_settings_pisignage";

    protected $primaryKey = "network_id";
    public $incrementing = false;

    public $timestamps = false;

    protected $touches = ["network"];

    public function network() {
        $this->belongsTo(Network::class, "network_id");
    }
}
