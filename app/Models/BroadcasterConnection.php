<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Services\Broadcast\Broadcaster;

/**
 * Class BroadcasterConnections
 *
 * Represent a connection to an external Digital Signage service
 *
 * @package Neo\Models
 *
 * @property int                                                     $id
 * @property string                                                  $uuid
 * @property string                                                  $broadcaster `\Neo\Enums\Broadcaster`
 * @property string                                                  $name
 * @property bool                                                    $active
 * @property Date                                                    $created_at
 * @property Date                                                    $updated_at
 * @property Date                                                    $deleted_at
 *
 * @property ConnectionSettingsBroadSign|ConnectionSettingsPiSignage $settings    Settings for the connection, dependant on the
 *           broadcaster type (`broadcaster`) of the connection. Defined in DBServiceProvider
 * @property Collection<DisplayType>                                 $display_types
 *
 */
class BroadcasterConnection extends Model {
    use HasFactory;
    use SoftDeletes;

    protected $table = "broadcasters_connections";

    protected $casts = [
        "active" => "bool"
    ];

    public function getSettingsAttribute() {
        switch ($this->broadcaster) {
            case Broadcaster::BROADSIGN:
                return $this->hasOne(ConnectionSettingsBroadSign::class, "connection_id")->getResults();
            case Broadcaster::PISIGNAGE:
                return $this->hasOne(ConnectionSettingsPiSignage::class, "connection_id")->getResults();
            default:
                return null;
        }
    }

    public function displayTypes(): HasMany {
        return $this->hasMany(DisplayType::class, "connection_id")->orderBy("name");
    }
}
