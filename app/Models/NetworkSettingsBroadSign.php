<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NetworkSettingsBroadSign
 *
 * @package Neo\Models
 * @property int     $network_id
 * @property int     $customer_id
 * @property int     $container_id
 * @property int     $tracking_id
 *
 * @property Network $network
 */
class NetworkSettingsBroadSign extends Model {
    use HasFactory;

    protected $table = "network_settings_broadsign";

    protected $primaryKey = "network_id";
    public $incrementing = false;

    public $timestamps = false;

    protected $touches = ["network"];

    public function network() {
        $this->belongsTo(Network::class, "network_id");
    }
}
