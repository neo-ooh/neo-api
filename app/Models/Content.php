<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Content.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;

use Illuminate\Auth\Access\Gate;
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
 * @property int                       layout_id
 * @property int                       broadsign_content_id
 * @property string                    name
 * @property double                    duration            How long this content will display. Not applicable if the content only has static creatives
 * @property bool                      is_editable         Tell if the content can be edited
 * @property bool                      is_approved         Tell if the content has been pre-approved
 * @property int                       scheduling_duration Maximum duration of a scheduling of this content.
 * @property int                       scheduling_times    How many times this content can be scheduled
 *
 * @property Actor                     owner               The actor who created this content
 * @property Library                   library             The library where this content resides
 * @property FormatLayout              layout              The layout of the content
 *
 * @property-read Collection<Creative> creatives           The content's creatives
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
        'layout_id',
        'name',
        'scheduling_duration',
        'scheduling_times',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_approved'         => 'boolean',
        'scheduling_duration' => 'integer',
        'scheduling_times'    => 'integer',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["creatives"];

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

    public static function boot(): void {
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

            /** @var Schedule $schedule */
            foreach ($content->schedules as $schedule) {
                // If a schedule has not be reviewed, we want to completely remove it
                if ($schedule->status === 'draft' || $schedule->status === 'pending') {
                    $schedule->forceDelete();
                } else {
                    $schedule->delete();
                }
            }
        });
    }

    protected static function newFactory(): ContentFactory {
        return ContentFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The Actor who owns this content
     * @return BelongsTo
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    /**
     * The Library hosting this content
     * @return BelongsTo
     */
    public function library(): BelongsTo {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }

    /**
     * The Content's creatives
     * @return HasMany
     */
    public function creatives(): HasMany {
        return $this->hasMany(Creative::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * The Schedules using this content
     * @return HasMany
     */
    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * The format of the content
     * @deprecated
     * @return BelongsTo
     */
    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }

    /**
     * The Content's Layout
     * @return BelongsTo
     */
    public function layout(): BelongsTo {
        return $this->belongsTo(FormatLayout::class, "layout_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * A content editable status is based on its scheduling. A content who has never been schedules, or only at the state of
     * drafts, will be editable. As soon as one of the content's schedule gets sent for review or reviewed, the content is
     * locked and cannot be edited again.
     *
     * @return bool True if the content can be edited
     */
    public function getIsEditableAttribute() {
        if($this->schedules_count === 0) {
            // No schedules, can be edited
            return true;
        }

        // If a content has been scheduled, it can still be edited if it has never been locked/approved, etc...
        return $this->schedules->every("status", "===", Schedule::STATUS_DRAFT);
    }
}
