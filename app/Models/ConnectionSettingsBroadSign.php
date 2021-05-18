<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * Class ConnectionSettingsBroadSign
 *
 * @package Neo\Models
 * @property int                   $domain_id
 * @property int                   $default_customer_id
 * @property int                   $default_tracking_id
 *
 * @property string                $certificate_path
 *
 * @property int                   $connection_id
 * @property BroadcasterConnection $connection
 */
class ConnectionSettingsBroadSign extends Model {
    use HasFactory;

    protected $table = "connection_settings_broadsign";

    protected $primaryKey = "connection_id";
    public $incrementing = false;

    public $timestamps = false;

    public function connection() {
        $this->belongsTo(BroadcasterConnection::class, "connection_id");
    }

    public function getCertificatePathAttribute() {
        Storage::path("secure/certs/{$this->connection->uuid}.pem");
    }
}
