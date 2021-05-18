<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property BroadcasterConnection $broadcaster_connection
 */
class ConnectionSettingsBroadSign extends Model {
    use HasFactory;

    protected $table = "connection_settings_broadsign";

    protected $primaryKey = "connection_id";
    public $incrementing = false;

    public $timestamps = false;

    protected $touches = ["broadcaster_connection"];

    public function broadcasterConnection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id");
    }

    public function getCertificatePathAttribute() {
        Storage::path("secure/certs/{$this->broadcaster_connection->uuid}.pem");
    }
}
