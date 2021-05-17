<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ConnectionSettingsPiSignage
 *
 * @package Neo\Models
 * @property string $token
 *
 * @property int    $connection_id
 */
class ConnectionSettingsPiSignage extends Model {
    use HasFactory;

    protected $table = "connection_settings_pisignage";

    protected $primaryKey = "connection_id";
    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = ["token"];

    protected $touches = ["connection"];

    public function connection() {
        $this->belongsTo(BroadcasterConnection::class, "connection_id");
    }
}
