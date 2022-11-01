<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Player.php
 */

namespace Neo\Modules\Broadcast\Models;


use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Class Player
 *
 * @package Neo\Models
 *
 * @property int       $id
 * @property int       $network_id
 * @property string    $external_id
 * @property int       $location_id
 * @property string    $name
 * @property Date      $created_at
 * @property Date      $updated_at
 * @property Date|null $deleted_at
 *
 * @property Network   $network
 * @property Location  $location
 *
 * @mixin Builder
 */
class Player extends Model {
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'players';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'network_id',
        'external_id',
        'location_id',
        'name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, "location_id");
    }


    /**
     * @return ExternalBroadcasterResourceId
     * @throws UnknownProperties
     */
    public function toExternalBroadcastIdResource(): ExternalBroadcasterResourceId {
        return new ExternalBroadcasterResourceId([
            "type"           => ExternalResourceType::Player,
            "broadcaster_id" => $this->network->connection_id,
            "external_id"    => $this->external_id,
        ]);
    }
}
