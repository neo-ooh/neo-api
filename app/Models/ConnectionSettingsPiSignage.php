<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ConnectionSettingsPiSignage
 *
 * @package Neo\Models
 * @property string                $server_url
 * @property string                $token
 *
 * @property int                   $connection_id
 * @property BroadcasterConnection $broadcaster_connection
 */
class ConnectionSettingsPiSignage extends Model {
    use HasFactory;

    protected $table = "connection_settings_pisignage";

    protected $primaryKey = "connection_id";
    public $incrementing = false;

    public $timestamps = false;

    protected $hidden = ["token"];

    protected $touches = ["broadcaster_connection"];

    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id");
    }
}
