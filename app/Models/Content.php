<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Content.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Neo\Models\Factories\ContentFactory;
use Neo\Rules\AccessibleContent;

/**
 * NeoModels\Contents
 *
 * @property int                       id
 * @property int                       owner_id
 * @property int                       library_id
 * @property int                       format_id
 * @property int                       broadsign_content_id
 * @property string                    name
 * @property double                    duration
 * @property int                       scheduling_duration
 * @property int                       scheduling_times
 *
 * @property Actor                     owner
 * @property Library                   library
 * @property Format                    format
 *
 * @property-read Collection<Creative> creatives
 * @property-read int                  creatives_count
 *
 * @property-read Collection<Schedule> schedules
 * @property-read int                  schedules_count
 *
 * @mixin DB
 */
class Content extends SecuredModel {
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Model properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'owner_id',
        'library_id',
        'format_id',
        'name',
        'scheduling_duration',
        'scheduling_times',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "broadsign_content_id",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'scheduling_duration' => 'integer',
        'scheduling_times'    => 'integer',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [ "creatives", "format:id,slug" ];

    /**
     * The relationship counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = [
        "schedules",
        "creatives",
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleContent::class;

    public static function boot (): void {
        parent::boot();

        static::deleting(function (Content $content) {
            /** @var Creative $creative */
            foreach ($content->creatives as $creative) {
                if ($content->isForceDeleting()) {
                    $creative->forceDelete();
                } else {
                    $creative->delete();
                }
            }

            /** @var Creative $creative */
            foreach ($content->schedules as $schedule) {
                $schedule->delete();
            }
        });
    }

    protected static function newFactory (): ContentFactory {
        return ContentFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Direct */

    /**
     * @return BelongsTo
     */
    public function owner (): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function library (): BelongsTo {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function creatives (): HasMany {
        return $this->hasMany(Creative::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function schedules (): HasMany {
        return $this->hasMany(Schedule::class, 'content_id', 'id')->withTrashed();
    }

    /* Network */

    /**
     * @return BelongsTo
     */
    public function format (): BelongsTo {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }
}
