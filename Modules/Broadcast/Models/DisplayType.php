<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayType.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Geocoder\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;

/**
 * Neo\Modules\Broadcast\Models\DisplayType
 *
 * @property int                          $id
 * @property int                          $connection_id
 * @property int                          $external_id
 * @property string                       $name
 * @property string                       $internal_name
 * @property int                          $width_px
 * @property int                          $height_px
 * @property Date                         $created_at
 * @property Date                         $updated_at
 *
 * @property BroadcasterConnection        $broadcaster_connection
 * @property Collection<Location>         $locations
 * @property Collection<DisplayTypeFrame> $frames
 *
 * @mixin Builder
 */
class DisplayType extends Model {
    use HasPublicRelations;

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
     * @var array<string>
     */
    protected $fillable = [
        "external_id",
        "connection_id",
        "name",
        "internal_name",
    ];

    public function getPublicRelations() {
        return [
            "frames" => Relation::make(
                load: "frames",
                gate: Capability::formats_crop_frames_edit,
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<BroadcasterConnection, DisplayType>
     */
    public function broadcaster_connection(): BelongsTo {
        return $this->belongsTo(BroadcasterConnection::class, "connection_id")->orderBy("name");
    }

    /**
     * @return HasMany<Location>
     */
    public function locations(): HasMany {
        return $this->hasMany(Location::class, "display_type_id", "id");
    }

    /**
     * @return BelongsToMany<Format>
     */
    public function formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "format_display_types", "display_type_id", "format_id");
    }

    /**
     * @return HasMany<DisplayTypeFrame>
     */
    public function frames(): HasMany {
        return $this->hasMany(DisplayTypeFrame::class, "display_type_id", "id")->orderBy("name");
    }
}
