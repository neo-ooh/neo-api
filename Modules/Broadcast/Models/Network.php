<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Network.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JsonException;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Field;
use Neo\Models\Property;
use Neo\Models\UnstructuredData\NetworkSettingsBroadSign;
use Neo\Models\UnstructuredData\NetworkSettingsPiSignage;
use Neo\Services\API\Traits\HasAttributes;
use Neo\Services\Broadcast\Broadcaster;
use RuntimeException;

/**
 * @property int                                               $id
 * @property string                                            $uuid
 * @property int                                               $connection_id
 * @property string                                            $name
 * @property string                                            $color
 * @property Date                                              $created_at
 * @property Date                                              $updated_at
 * @property Date                                              $deleted_at
 *
 * @property NetworkSettingsBroadSign|NetworkSettingsPiSignage $settings
 * @property BroadcasterConnection                             $broadcaster_connection
 * @property Collection<Location>                              $locations
 * @property Collection<Campaign>                              $campaigns
 * @property Collection<Field>                                 $properties_fields
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

    protected $casts = [
        "settings" => "array",
    ];

    protected $hidden = [
        "settings",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id")->orderBy("name");
    }

    /**
     * @throws JsonException
     */
    public function getSettingsAttribute(): NetworkSettingsBroadSign|NetworkSettingsPiSignage|null {
        $settings = $this->attributes["settings"] !== null ? json_decode($this->attributes["settings"], true, 512, JSON_THROW_ON_ERROR) : [];

        return match ($this->broadcaster_connection->broadcaster) {
            Broadcaster::BROADSIGN => new NetworkSettingsBroadSign($settings),
            Broadcaster::PISIGNAGE => new NetworkSettingsPiSignage($settings),
            default                => null,
        };
    }

    public function setSettingsAttribute($value): void {
        if (!in_array(HasAttributes::class, class_uses($value), true)) {
            throw new RuntimeException("Bad format");
        }

        $this->attributes["settings"] = $value->toJson();
    }


    public function locations(): HasMany {
        return $this->hasMany(Location::class, 'network_id', 'id')->orderBy("name");
    }

    public function campaigns(): HasMany {
        return $this->hasMany(Campaign::class, 'network_id', 'id')->orderBy("name");
    }

    public function properties_fields(): BelongsToMany {
        return $this->belongsToMany(Field::class, "fields_networks", "network_id", "field_id")
                    ->withPivot(["order"])
                    ->orderByPivot("order");
    }

    public function getPropertiesAttribute() {
        $networkId = $this->id;
        return Property::query()->whereHas("actor.own_locations", function (Builder $query) use ($networkId) {
            $query->where("network_id", "=", $networkId);
        })->get()->sortBy("actor.name");
    }
}
