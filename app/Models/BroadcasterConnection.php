<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterConnection.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JsonException;
use Neo\Models\UnstructuredData\ConnectionSettingsBroadSign;
use Neo\Models\UnstructuredData\ConnectionSettingsOdoo;
use Neo\Models\UnstructuredData\ConnectionSettingsPiSignage;
use Neo\Services\API\Traits\HasAttributes;
use Neo\Services\Broadcast\Broadcaster;
use RuntimeException;

/**
 * Class BroadcasterConnections
 *
 * Represent a connection to an external Digital Signage service
 *
 * @package Neo\Models
 *
 * @property int                                                     $id
 * @property string                                                  $uuid
 * @property string                                                  $broadcaster
 *           `\Neo\Services\Broadcast\Broadcaster`
 * @property string                                                  $name
 * @property bool                                                    $active
 * @property Date                                                    $created_at
 * @property Date                                                    $updated_at
 * @property Date                                                    $deleted_at
 *
 * @property ConnectionSettingsBroadSign|ConnectionSettingsPiSignage $settings    Settings for the
 *           connection, dependant on the broadcaster type (`broadcaster`) of the connection. Defined in DBServiceProvider
 * @property Collection<DisplayType>                                 $display_types
 *
 */
class BroadcasterConnection extends Model {
    use HasFactory;
    use SoftDeletes;

    protected $table = "broadcasters_connections";

    protected $casts = [
        "active"   => "bool",
        "settings" => "array",
    ];

    protected $hidden = [
        "settings"
    ];

    public function displayTypes(): HasMany {
        return $this->hasMany(DisplayType::class, "connection_id")->orderBy("name");
    }

    /**
     * @throws JsonException
     */
    public function getSettingsAttribute() {
        $settings = $this->attributes["settings"] !== null ? json_decode($this->attributes["settings"], true, 512, JSON_THROW_ON_ERROR) : [];

        $settings["broadcaster_uuid"] = $this->uuid;

        return match ($this->broadcaster) {
            Broadcaster::BROADSIGN => new ConnectionSettingsBroadSign($settings),
            Broadcaster::PISIGNAGE => new ConnectionSettingsPiSignage($settings),
            default => null,
        };
    }

    public function setSettingsAttribute($value) {
        if (!in_array(HasAttributes::class, class_uses($value), true)) {
            throw new RuntimeException("Bad format");
        }

        $this->attributes["settings"] = $value->toJson();
    }
}
