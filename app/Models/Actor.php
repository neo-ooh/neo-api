<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Firebase\JWT\JWT;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Neo\Models\Traits\HasHierarchy;
use Neo\Models\Traits\HasLocations;
use Neo\Models\Traits\HasRoles;
use Neo\Models\Traits\WithRelationCaching;
use Neo\Rules\AccessibleActor;

/**
 * Class Actor
 *
 * @package Neo\Base
 *
 * @property int            id
 * @property string         name
 * @property string         email
 * @property string         password
 * @property bool           is_group
 * @property bool           is_locked
 * @property int            locked_by
 * @property int|null       branding_id
 * @property bool           tos_accepted
 *
 * @property Date           created_at
 * @property Date           updated_at
 * @property Date           last_login_at
 *
 * @property TwoFactorToken twoFactorToken
 * @property RecoveryToken  recoveryToken
 * @property SignupToken    signupToken
 *
 * @property Collection     accessible_actors
 * @property Collection     shared_actors
 * @property int|null       parent_id
 * @property Actor          parent
 *
 * @property Collection     sharings
 * @property Collection     sharers
 *
 * @property Collection     locations
 * @property Branding|null  branding
 *
 * @property Collection     own_libraries
 * @property Collection     shared_libraries
 * @property Collection     parent_libraries
 * @property Collection     children_libraries
 * @property Collection     libraries
 *
 *
 * @method Builder    accessibleActors()
 * @method Builder      SharedActors()
 *
 *
 * @mixin Builder
 */
class Actor extends SecuredModel implements AuthenticatableContract, AuthorizableContract {
    use Notifiable, Authenticatable, Authorizable;
    use HasFactory;
    use HasLocations;
    use HasRoles;
    use HasHierarchy;
    use Traits\HasCampaigns;
    use WithRelationCaching;

    // Details properties
    public ?int $parent_id = null;
    public bool $parent_is_group = false;
    public int $direct_children_count = 0;
    public string $path_names = "";
    public string $path_ids = "";

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
    protected $table = "actors";

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "last_login_at",
    ];

    /**
     * The attributes that should not be included in serialization
     *
     * @var array
     */
    protected $hidden = [
        "password",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "is_group"     => "boolean",
        "is_locked"    => "boolean",
        "tos_accepted" => "boolean",
    ];

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array
     */
    protected $appends = [
        "parent_id",
        "parent_is_group",
        "direct_children_count",
        "path_names",
        "path_ids",
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleActor::class;

    public static function boot(): void {
        parent::boot();

        static::retrieved(function (Actor $model) {
            $model->loadDetails();
        });
    }

    protected static function newFactory(): Factories\ActorFactory {
        return Factories\ActorFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Gets the list of all users who have access to this user"s descendants
     */
    public function sharings(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, "actors_shares", "sharer_id", "shared_with_id")
                    ->withTimestamps();
    }

    /**
     * Gets the list of all users sharing their descendants with the current one
     */
    public function sharers(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, "actors_shares", "shared_with_id", "sharer_id")
                    ->withTimestamps();
    }

    /**
     * Give the user who locked this user, if applicable
     */
    public function lockedBy(): BelongsTo {
        return $this->belongsTo(__CLASS__, "locked_by");
    }

    /**
     * Give the user who locked this user, if applicable
     */
    public function twoFactorToken(): HasOne {
        return $this->hasOne("Neo\Models\TwoFactorToken");
    }

    /**
     * Give the user who locked this user, if applicable
     */
    public function signupToken(): HasOne {
        return $this->hasOne("Neo\Models\SignupToken");
    }

    /**
     * Give the user who locked this user, if applicable
     */
    public function recoveryToken(): HasOne {
        return $this->hasOne("Neo\Models\RecoveryToken", "email", "email");
    }


    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */

    /**
     * Gets the user"s applied branding
     */
    public function branding(): BelongsTo {
        return $this->belongsTo(Branding::class);
    }

    public function getAppliedBrandingAttribute(): ?Branding {
        if ($this->branding) {
            return $this->branding;
        }

        /** @var Actor $parent */
        $parent = $this->Parents()->whereHas("branding")->first();

        if (is_null($parent)) {
            return null;
        }

        return $parent->branding;
    }


    /*
    |--------------------------------------------------------------------------
    | Hierarchy & co.
    |--------------------------------------------------------------------------
    */

    /**
     * Scope
     *
     * @return Builder
     */
    public function scopeAccessibleActors(): Builder {
        return $this->selectActors()
                    ->Children()
                    ->union($this->selectActors()
                                 ->SharedActors()
                                 ->distinct())
                    ->orderBy("name");
    }

    /**
     * Gives all actors this actor is allowed to interact with
     *
     * @return Collection
     */
    public function getAccessibleActorsAttribute(): Collection {
        // We have access to all our children and the descendants of all the items who shared their pool with us
        return $this->newQuery()->AccessibleActors()->get();
    }

    public function scopeSharedActors(Builder $query): Builder {
        $ancestorColumn   = $this->getQualifiedClosureColumn("ancestor_id");
        $descendantColumn = $this->getQualifiedClosureColumn("descendant_id");

        return $query->join("actors_closures",
            function (JoinClause $join) use ($descendantColumn) {
                $join->on($descendantColumn, "=", "actors.id");
            })
                     ->join("actors_shares as s",
                         function (JoinClause $join) use ($descendantColumn, $ancestorColumn) {
                             $join->on("s.sharer_id", "=", $ancestorColumn);
                             $join->on("s.sharer_id", "<>", $descendantColumn);
                         })
                     ->where("s.shared_with_id", "=", $this->getKey());
    }

    public function getSharedActorsAttribute() {
        return $this->selectActors()->SharedActors()->select($this->qualifyColumn("*"))->get();
    }

    /**
     * Return the group this item is a part of. If the item is not part of any group, null is returned
     *
     * @return Actor|null
     */
    public function getGroupAttribute(): ?Actor {
        if ($this->parent_is_group) {
            return $this->parent;
        }

        return null;
    }

    public function loadDetails(): void {
        $details = DB::selectOne("SELECT * FROM `actors_details` WHERE `id` = ?", [$this->getKey()]);

        $this->parent_id             = $details->parent_id;
        $this->parent_is_group       = $details->parent_is_group;
        $this->direct_children_count = $details->direct_children_count;
        $this->path_names            = $details->path_names;
        $this->path_ids              = $details->path_ids;
    }

    public function getParentIdAttribute(): ?int {
        return $this->parent_id;
    }

    public function getParentIsGroupAttribute(): bool {
        return $this->parent_is_group;
    }

    public function getDirectChildrenCountAttribute(): int {
        return $this->direct_children_count;
    }

    public function getPathNamesAttribute(): string {
        return $this->path_names;
    }

    public function getPathIdsAttribute(): string {
        return $this->path_ids;
    }

    public function summary(): array {
        return ["id"          => $this->id,
                "name"        => $this->name,
                "email"       => $this->email,
                "is_group"    => $this->is_group,
                "branding_id" => $this->branding_id,
        ];
    }

    /*
    |----------------------------------------------------------------|
    | Relations
    |----------------------------------------------------------------|
    */

    /**
     * Tell if the current user has access to the given one, either by being one of its parent or through a sharing
     *
     * @param Actor $node
     *
     * @return bool
     */
    public function hasAccessTo(Actor $node): bool {
        // Start by checking if we are a parent of the actor
        if ($this->isParentOf($node)) {
            return true;
        }

        // We are not a parent of the given actor, is it shared with us ?
        return $this->shared_actors->pluck("id")->contains($node->id);
    }

    /*
    |----------------------------------------------------------------|
    | Libraries
    |----------------------------------------------------------------|
    */

    protected function getLibrariesAttribute(): Collection {
        $libraries = new Collection();
        $libraries = $libraries->merge($this->own_libraries);
        $libraries = $libraries->merge($this->children_libraries);
        $libraries = $libraries->merge($this->shared_libraries);
        $libraries = $libraries->merge($this->parent_libraries);
        $libraries = $libraries->unique("id");
        return $libraries;
    }

    protected function getOwnLibrariesAttribute(): ?Collection {
        return $this->getCachedRelation("own_libraries", fn() => Library::of($this)->get());
    }

    protected function getChildrenLibrariesAttribute(): ?Collection {
        return $this->getCachedRelation("children_libraries", fn() => Library::ofChildrenOf($this)->get());
    }

    public function getParentLibrariesAttribute(): ?Collection {
        if ($this->parent_is_group) {
            return $this->getCachedRelation("parent_libraries", fn() => Library::of($this->parent)->get());
        }

        return new Collection();
    }

    public function shared_libraries(): ?BelongsToMany {
        return $this->belongsToMany(Library::class, "library_shares", "actor_id", "library_id");
    }

    /*
    |----------------------------------------------------------------|
    | Accessors & Mutators
    |----------------------------------------------------------------|
    */

    /**
     * Set the user password, hashing it beforehand for security
     *
     * @param $value
     */
    public function setPasswordAttribute($value): void {
        $this->attributes["password"] = Hash::make($value);
    }

    public function withDetails(): self {
        if ($this->is_locked) {
            $this->load("lockedBy");
        }

        $this->append(["parent"]);
        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @return bool
     */
    public function is2FAValid(): bool {
        $token = $this->twoFactorToken;

        // Is there a token ?
        if ($token === null) {
            return false;
        }

        // Is the token validated and part of the current user session
        if ($token->validated && $token->created_at >= $this->last_login_at) {
            return true;
        }

        return false;
    }

    /**
     * Build and return a JWT for the current user.q
     *
     * @return string
     */
    public function getJWT(): string {
        $payload = [
            // Registered
            "iss"  => config("app.url"),
            "aud"  => "*.neo-ooh.info",
            "iat"  => time(),

            // Private
            "uid"  => $this->id,
            "name" => $this->name,
            "2fa"  => $this->is2FAValid(),
            "tos"  => $this->tos_accepted,
        ];

        return JWT::encode($payload, config("auth.jwt_private_key"), "RS256");
    }
}
