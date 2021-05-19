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
 * @property string                $file_name
 * @property string                $file_path
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

    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id");
    }

    public function getCertificatePathAttribute() {
        return Storage::path("secure/certs/");
    }

    public function getFileNameAttribute() {
        return "{$this->broadcaster_connection->uuid}.pem";
    }
    public function getFilePathAttribute() {
        return $this->certificate_path . $this->file_name;
    }
}
