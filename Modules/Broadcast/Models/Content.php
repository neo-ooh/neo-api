<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Content.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Neo\Models\Actor;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Rules\AccessibleContent;
use Neo\Modules\Broadcast\Services\Resources\Content as ContentResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Neo\Models\Contents
 *
 * A Content is a resource that can be scheduled in a `Campaign` with the mean of a `Schedule`.
 *
 * @property int                       $id
 * @property int                       $owner_id
 * @property int                       $library_id
 * @property int                       $layout_id
 * @property string                    $name
 * @property double                    $duration              How long this content will display. Not applicable if the content
 *           only has static creatives
 * @property bool                      $is_approved           Tell if the content has been pre-approved
 * @property int                       $max_schedule_duration Maximum duration of a scheduling of this content.
 * @property int                       $max_schedule_count    How many times this content can be scheduled
 *
 * @property bool                      $is_editable           Tell if the content can be edited
 *
 * @property Actor                     $owner                 The actor who created this content
 * @property Library                   $library               The library where this content resides
 * @property Layout                    $layout                The layout of the content
 *
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 * @property Carbon|null               $deleted_at
 *
 * @property-read Collection<Creative> $creatives             The content's creatives
 * @property-read int                  $creatives_count
 *
 * @property-read Collection<Schedule> $schedules
 * @property-read int                  $schedules_count
 *
 * @mixin DB
 */
class Content extends BroadcastResourceModel {
    use SoftDeletes;
    use HasPublicRelations;

    /*
    |--------------------------------------------------------------------------
    | OdooModel properties
    |--------------------------------------------------------------------------
    */

    public BroadcastResourceType $resourceType = BroadcastResourceType::Content;

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
        'duration',
        'max_schedule_duration',
        'max_schedule_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_approved'           => 'boolean',
        'max_schedule_duration' => 'integer',
        'max_schedule_count'    => 'integer',
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleContent::class;

    protected function getPublicRelations() {
        return [
            "creatives"       => "creatives",
            "external_ids"    => "creatives.external_representations",
            "layout"          => ["layout", "layout.frames"],
            "library"         => "library",
            "owner"           => "owner",
            "schedules"       => [
                "schedules.broadcast_tags",
                "schedules.owner:id,name",
                "schedules.campaign.parent:id,name",
            ],
            "schedules_count" => "count:schedules",
        ];
    }

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Content $content) {
            /** @var Collection<Creative> $creatives */
            $creatives = $content->creatives()->withTrashed()->get();

            foreach ($creatives as $creative) {
                if ($content->isForceDeleting()) {
                    $creative->forceDelete();
                } else {
                    $creative->delete();
                }
            }

            /** @var Collection<Schedule> $schedules */
            $schedules = $content->schedules()->withTrashed()->get();

            foreach ($schedules as $schedule) {
                // If a schedule has not be reviewed, we want to completely remove it
                if (($schedule->status === ScheduleStatus::Draft || $schedule->status === ScheduleStatus::Pending) || $schedule->isForceDeleting()) {
                    $schedule->forceDelete();
                } else {
                    $schedule->delete();
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The Actor who owns this content
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    /**
     * The Library hosting this content
     *
     * @return BelongsTo
     */
    public function library(): BelongsTo {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }

    /**
     * The Content's creatives
     *
     * @return HasMany
     */
    public function creatives(): HasMany {
        return $this->hasMany(Creative::class, 'content_id', 'id');
    }

    /**
     * The Schedules using this content
     *
     * @return HasMany
     */
    public function schedules(): HasMany {
        return $this->hasMany(Schedule::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * The Content's Layout
     *
     * @return BelongsTo
     */
    public function layout(): BelongsTo {
        return $this->belongsTo(Layout::class, "layout_id", "id")->withTrashed();
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
    public function getIsEditableAttribute(): bool {
        if ($this->schedules_count === 0) {
            // No schedules, can be edited
            return true;
        }

        // If a content has been scheduled, it can still be edited if it has never been locked/approved, etc...
        return $this->schedules->every("status", "===", ScheduleStatus::Draft);
    }


    /**
     * @throws UnknownProperties
     */
    public function toResource(): ContentResource {
        return new ContentResource([
            "name"          => $this->name,
            "duration_msec" => $this->duration,
            "is_fullscreen" => $this->layout->is_fullscreen,
        ]);
    }
}
