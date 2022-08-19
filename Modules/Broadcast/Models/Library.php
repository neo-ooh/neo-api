<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Library.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Neo\Models\Actor;
use Neo\Models\Factories\LibraryFactory;
use Neo\Models\SecuredModel;
use Neo\Models\Traits\WithPublicRelations;
use Neo\Modules\Broadcast\Rules\AccessibleLibrary;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Neo\Models\Branding
 *
 * @property int                 $id
 * @property int                 $owner_id
 * @property string              $name
 * @property int                 $content_limit
 *
 * @property int                 $contents_count
 *
 * @property Actor               $owner
 * @property Collection<Content> $contents
 * @property Collection<Actor>   $shares
 * @property Collection<Format>  $formats
 * @property Collection<Layout>  $layouts
 *
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 *
 * @mixin Builder
 */
class Library extends SecuredModel {
    use HasFactory;
    use Notifiable;
    use WithPublicRelations;
    use HasRelationships;

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
    protected $table = 'libraries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'content_limit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content_limit'  => 'integer',
        'hidden_formats' => 'array',
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var class-string
     */
    protected string $accessRule = AccessibleLibrary::class;

    protected array $publicRelations = [
        "contents" => "contents",
        "formats"  => "formats",
        "layouts"  => "layouts",
        "shares"   => "shares",
    ];

    protected static function boot(): void {
        parent::boot();

        /**
         * On library deletion, also deletes all its contents
         */
        static::deleting(static function (Library $library) {
            /** @var Content $content */
            foreach ($library->contents as $content) {
                $content->delete();
            }
        });
    }

    protected static function newFactory(): LibraryFactory {
        return LibraryFactory::new();
    }


    /**
     * Gets all libraries owned by the given actor
     *
     * @param Actor $actor
     *
     * @return Builder
     */
    public static function of(Actor $actor): Builder {
        return static::selectLibraries()
                     ->where("l.owner_id", "=", $actor->getKey());
    }

    /**
     * Gets a new query for selecting libraries
     *
     * @return Builder
     */
    protected static function selectLibraries(): Builder {
        return static::query()
                     ->select("l.*", DB::raw("COUNT(c.id) AS contents_count"))
                     ->from("libraries", "l")
                     ->leftJoin("contents AS c", "c.library_id", "=", "l.id")
                     ->groupBy("l.id")
                     ->orderBy("l.name");
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Direct */

    /**
     * Gets all libraries shared with the given actor
     *
     * @param Actor $actor
     *
     * @return Builder
     */
    public static function sharedWith(Actor $actor): Builder {
        return static::selectLibraries()
                     ->join("library_shares as ls",
                         function (JoinClause $join) {
                             $join->on("ls.library_id", "=", "l.id");
                         })
                     ->where("ls.actor_id", "=", $actor->getKey());
    }

    /**
     * Gets all libraries of children of the given actor. Includes libraries of user shared with the given user.
     *
     * @param Actor $actor
     *
     * @return Builder
     */
    public static function ofChildrenOf(Actor $actor): Builder {
        return static::selectLibraries()
                     ->whereIn("l.owner_id", $actor->getAccessibleActors(true, false, false, false)
                                                   ->pluck("id"));
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Actor, Library>
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    /**
     * @return BelongsToMany<Actor>
     */
    public function shares(): BelongsToMany {
        return $this->belongsToMany(Actor::class, 'library_shares', 'library_id', 'actor_id')
                    ->withTimestamps();
    }

    /**
     * @return HasMany<Content>
     */
    public function contents(): HasMany {
        return $this->hasMany(Content::class, 'library_id', 'id');
    }

    /**
     * @return BelongsToMany<Format>
     */
    public function formats() {
        return $this->belongsToMany(Format::class, "library_formats", "library_id", "format_id");
    }

    /**
     * @return HasManyDeep<Layout>
     */
    public function layouts(): HasManyDeep {
        return $this->hasManyDeepFromRelations($this->formats(), (new Format())->layouts());
    }


    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    public function isAccessibleBy(Actor $actor): bool {
        // Is the actor the owner ?
        if ($actor->getKey() === $this->owner_id) {
            return true;
        }

        // Is the actor's parent a group ?
        if ($actor->parent->is_group ?? false) {
            // Does the library belongs to it ?
            return $this->isAccessibleBy($actor->parent);
        }

        // Is the library shared with the given actor ?
        if (DB::selectOne("SELECT COUNT(`ls`.`actor_id`) AS `shared` FROM `library_shares` `ls` WHERE `library_id` = ? AND `actor_id` = ?",
                [
                    $this->getKey(),
                    $actor->getKey(),
                ])->shared === 1) {
            return true;
        }


        // Is the owner a child of the actor ?
        if ($actor->accessibleActors()->get("id")->pluck("id")->contains($this->owner_id)) {
            return true;
        }

        return false;
    }
}
