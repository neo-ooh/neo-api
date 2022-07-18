<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayType.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\BroadcasterConnection;

/**
 * Neo\Modules\Broadcast\Models\DisplayType
 *
 * @property int                   $id
 * @property int                   $connection_id
 * @property int                   $external_id
 * @property string                $name
 * @property string                $internal_name
 * @property Date                  $created_at
 * @property Date                  $updated_at
 *
 * @property BroadcasterConnection $broadcaster_connection
 *
 * @mixin Builder
 */
class DisplayType extends Model {
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
    protected $table = 'display_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "external_id",
        "connection_id",
        "name",
        "interna_name",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Network */

    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id")->orderBy("name");
    }

    public function locations(): HasMany {
        return $this->hasMany(Location::class, "display_type_id", "id");
    }

    public function formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "formats_display_types", "display_type_id", "format_id");
    }
}
