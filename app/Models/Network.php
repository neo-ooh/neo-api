<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Network.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Services\Broadcast\Broadcaster;

/**
 * NeoModels\Branding
 *
 * @property int                                               $id
 * @property string                                            $uuid
 * @property int                                               $connection_id
 * @property string                                            $name
 * @property Date                                              $created_at
 * @property Date                                              $updated_at
 * @property Date                                              $deleted_at
 *
 * @property NetworkSettingsBroadSign|NetworkSettingsPiSignage $settings
 * @property BroadcasterConnection                             $broadcaster_connection
 * @property Collection<Location>                              $locations
 * @property Collection<Campaign>                              $campaigns
 *
 * @mixin Builder
 */
class Network extends Model {
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'networks';

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id")->orderBy("name");
    }

    public function getSettingsAttribute() {
        switch ($this->broadcaster_connection->broadcaster) {
            case Broadcaster::BROADSIGN:
                return $this->hasOne(NetworkSettingsBroadSign::class, "network_id")->getResults();
            case Broadcaster::PISIGNAGE:
                return $this->hasOne(NetworkSettingsPiSignage::class, "network_id")->getResults();
            default:
                return null;
        }
    }

    public function locations(): HasMany {
        return $this->hasMany(Location::class, 'network_id', 'id')->orderBy("name");
    }

    public function campaigns(): HasMany {
        return $this->hasMany(Campaign::class, 'network_id', 'id')->orderBy("name");
    }
}
