<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Actor.php
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Neo\Models\Traits\HasCampaigns;
use Neo\Models\Traits\HasCapabilities;
use Neo\Models\Traits\HasHierarchy;
use Neo\Models\Traits\HasLocations;
use Neo\Models\Traits\HasRoles;
use Neo\Models\Traits\WithRelationCaching;
use Neo\Modules\Broadcast\Enums\CampaignStatus;
use Neo\Modules\Broadcast\Models\Library;
use Neo\Rules\AccessibleActor;

/**
 * Class Actor
 *
 * @package Neo\Base
 *
 * @property int                 $id
 * @property string              $name
 * @property string              $email
 * @property string              $password
 * @property string              $locale
 * @property bool                $is_group              Tell if the current actor is a group
 * @property bool                $is_property           Tell if the current actor is a property. A property is always  group,
 *           never a user.
 * @property bool                $is_locked             Tell if the current actor has been locked. A locked actor cannot login
 * @property int|null            $locked_by             Tell who locke this actor, if applicable
 * @property int|null            $branding_id           ID of the branding applied to this user
 *
 * @property Date                $created_at
 * @property Date                $updated_at
 * @property Date                $last_login_at
 *
 * @property bool                $registration_sent     Tell if the registration email was sent to the actor. Not applicable to
 *           groups
 * @property bool                $is_registered         Tell if the user has registered its account. Not applicable to groups
 * @property bool                $tos_accepted          Tell if the actor has accepted the current version of the TOS. Not
 *           applicable to groups
 * @property bool                $limited_access        If set, the actor does not have access to its group and group's children
 *           campaigns. Only to its own, its children campaigns and with user shared with it.
 *
 * @property int|null            $phone_id
 * @property Phone|null          $phone
 *
 * @property string              $two_fa_method
 * @property TwoFactorToken|null $twoFactorToken
 * @property RecoveryToken       $recoveryToken
 * @property SignupToken|null    $signupToken
 *
 *
 * @property Property            $property
 *
 * @property Collection          $accessible_actors
 * @property Collection          $shared_actors
 * @property int|null            $parent_id
 * @property Actor               $parent
 *
 * @property Collection          $sharings
 * @property Collection          $sharers
 *
 * @property Collection          $locations
 * @property Branding|null       $branding
 *
 * @property Collection          $own_libraries
 * @property Collection          $shared_libraries
 * @property Collection          $children_libraries
 *
 * @property Collection          $campaign_planner_saves
 * @property Collection          $campaign_planner_polygons
 *
 * @property ?ActorLogo          $logo
 *
 * @property string              $campaigns_status
 * @property Collection<Tag>     $tags
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
    use HasCampaigns;
    use HasCapabilities;
    use WithRelationCaching;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "actors";

    /**
     * The attributes that should not be included in serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "details"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_group"       => "boolean",
        "is_property"    => "boolean",
        "is_locked"      => "boolean",
        "tos_accepted"   => "boolean",
        "limited_access" => "boolean",
        "last_login_at"  => "date"
    ];

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array<string>
     */
    protected $with = [
        "details",
    ];

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array<string>
     */
    protected $appends = [
        "parent_id",
        "parent_is_group",
        "is_property",
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
     *
     * @return BelongsToMany<static>
     */
    public function sharings(): BelongsToMany {
        return $this->belongsToMany(static::class, "actors_shares", "sharer_id", "shared_with_id")
                    ->withTimestamps();
    }

    /**
     * Gets the list of all users sharing their descendants with the current one
     *
     * @return BelongsToMany<static>
     */
    public function sharers(): BelongsToMany {
        return $this->belongsToMany(static::class, "actors_shares", "shared_with_id", "sharer_id")
                    ->withTimestamps();
    }

    /**
     * Give the user who locked this user, if applicable
     *
     * @return BelongsTo<static, static>
     */
    public function lockedBy(): BelongsTo {
        return $this->belongsTo(static::class, "locked_by");
    }

    /**
     * Give the user who locked this user, if applicable
     *
     * @return HasOne<TwoFactorToken>
     */
    public function twoFactorToken(): HasOne {
        return $this->hasOne(TwoFactorToken::class);
    }

    /**
     * Give the user who locked this user, if applicable
     *
     * @return HasOne<SignupToken>
     */
    public function signupToken(): HasOne {
        return $this->hasOne(SignupToken::class);
    }

    /**
     * Give the user who locked this user, if applicable
     *
     * @return HasOne<RecoveryToken>
     */
    public function recoveryToken(): HasOne {
        return $this->hasOne(RecoveryToken::class, "email", "email");
    }

    /**
     * Load details about the actor hierarchy
     *
     * @return HasOne<ActorDetails>
     */
    public function details(): HasOne {
        return $this->hasOne(ActorDetails::class, 'id', 'id');
    }

    /**
     * The actor's logo
     *
     * @return HasOne<ActorLogo>
     */
    public function logo(): HasOne {
        return $this->hasOne(ActorLogo::class, 'actor_id', 'id');
    }

    /**
     * @return HasOne<Property>
     */
    public function property(): HasOne {
        return $this->hasOne(Property::class, 'actor_id', 'id');
    }

    /**
     * @return BelongsTo<Phone, Actor>
     */
    public function phone(): BelongsTo {
        return $this->belongsTo(Phone::class, 'phone_id', 'id');
    }

    /**
     * @return HasMany<CampaignPlannerSave>
     */
    public function campaign_planner_saves(): HasMany {
        return $this->hasMany(CampaignPlannerSave::class, 'actor_id', 'id');
    }

    /**
     * @return HasMany<CampaignPlannerPolygon>
     */
    public function campaign_planner_polygons(): HasMany {
        return $this->hasMany(CampaignPlannerPolygon::class, 'actor_id', 'id');
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany {
        return $this->belongsToMany(Tag::class, "actors_tags", "actor_id", "tag_id");
    }

    /**
     * @return BelongsTo<Actor, Actor>
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(static::class, "parent_id", "id");
    }

    /**
     * @return BelongsToMany<Library>
     */
    public function shared_libraries(): BelongsToMany {
        return $this->belongsToMany(Library::class, "library_shares", "actor_id", "library_id");
    }


    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */

    /**
     * Gets the user"s applied branding
     *
     * @return BelongsTo<Branding, Actor>
     */
    public function branding(): BelongsTo {
        return $this->belongsTo(Branding::class);
    }

    public function getAppliedBrandingAttribute(): ?Branding {
        if ($this->branding) {
            return $this->branding;
        }

        /** @var Actor|null $parent */
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
     * @param bool $children
     * @param bool $shallow
     * @param bool $shared
     * @param bool $parent
     * @return \Illuminate\Support\Collection
     */
    public function getAccessibleActors(bool $children = true, bool $shallow = false, bool $shared = true, bool $parent = true): \Illuminate\Support\Collection {
        /** @var Collection<Actor> $actors */
        $actors = new Collection();

        if ($children && $shallow) {
            $actors = $actors->merge($this->direct_children);
        }

        if ($children && !$shallow) {
            $actors = $actors->merge($this->children);
        }

        if ($shared) {
            $actors = $actors->merge($this->shared_actors);
        }

        if ($parent && !$this->is_group && ($this->parent_is_group ?? false)) {
            $actors->push($this->parent);
            $actors = $actors->merge($this->parent->getAccessibleActors(!$this->limited_access, false, !$this->limited_access, false));
        }

        // By default, a user cannot see itself
        $actors = $actors->filter(fn(Actor $actor) => $actor->id !== Auth::id());

        return $actors->values()->unique("id");
    }

    /**
     * Scope to all actors the current user has access to. Use
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
     * @return Collection<Actor>
     */
    public function getAccessibleActorsAttribute(): Collection {
        // We have access to all our children and the descendants of all the items who shared their pool with us as well as all descendants and accessible actors of our parent if it is a group. We do not use the parent `accessible users` property as we don't want to get recursive.
        /** @var Collection<Actor> $accessible */
        $accessible = $this->newQuery()->AccessibleActors()->get();

        // A user can access its group actors, but a group cannot access its parent actors, even if it is a group.
        if (($this->parent->is_group ?? false) && !$this->is_group) {
            $accessible = $accessible->merge($this->parent->children);
            $accessible = $accessible->merge($this->parent->shared_actors);
        }

        return $accessible->unique("id");
    }

    public function scopeSharedActors(Builder $query): Builder {
        $ancestorColumn   = $this->getQualifiedClosureColumn("ancestor_id");
        $descendantColumn = $this->getQualifiedClosureColumn("descendant_id");

        return $query->join("actors_closures",
            function (JoinClause $join) use ($descendantColumn) {
                $join->on($descendantColumn, "=", "actors.id");
            })
                     ->join("actors_shares as s",
                         function (JoinClause $join) use ($ancestorColumn) {
                             $join->on("s.sharer_id", "=", $ancestorColumn);
//                             $join->on("s.sharer_id", "<>", $descendantColumn);
                         })
                     ->where("s.shared_with_id", "=", $this->getKey());
    }

    /**
     * @return Collection<Actor>
     */
    public function getSharedActorsAttribute(): Collection {
        return $this->selectActors()->SharedActors()->select($this->qualifyColumn("*"))->get();
    }

    /**
     * Return the group this item is a part of. If the item is not part of any group, null is returned
     *
     * @return Actor|null
     */
    public function getGroupAttribute(): ?self {
        if (($this->parent->is_group ?? false)) {
            return $this->parent;
        }

        return null;
    }

    public function getParentIdAttribute(): ?int {
        return $this->details->parent_id;
    }

    public function getParentIsGroupAttribute(): bool {
        return $this->details->parent_is_group;
    }

    public function getIsPropertyAttribute(): ?bool {
        return $this->details->is_property;
    }

    public function getDirectChildrenCountAttribute(): int {
        return $this->details->direct_children_count;
    }

    public function getPathNamesAttribute(): string {
        return $this->details->path_names;
    }

    public function getPathIdsAttribute(): string {
        return $this->details->path_ids;
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

        // We are not a parent of the given actor, is it shared with us or part of our parent hierarchy if its a group ?
        return $this->getAccessibleActors()->pluck("id")->contains($node->id);
    }

    /*
    |----------------------------------------------------------------|
    | Libraries
    |----------------------------------------------------------------|
    */

    /**
     * List all the libraries this actor has access to. The flags allow for specifying which type of related libraries are
     * returned.
     *
     * @param bool $own      True to list the actor's own libraries
     * @param bool $shared   True to list libraries shared with the actor
     * @param bool $children True to list this actor's children's libraries
     * @param bool $parent   True to list the parent of the actor's libraries, libraries shared with it, and its parent library
     *                       if its a group.
     * @return Collection
     */
    public function getLibraries(bool $own = true, bool $shared = true, bool $children = true, bool $parent = true): Collection {
        $libraries = new Collection();

        // Actor's own libraries
        if ($own) {
            $libraries = $libraries->merge($this->own_libraries);
        }

        // Libraries shared with the actor
        if ($shared) {
            $libraries = $libraries->merge($this->shared_libraries);
            $libraries = $libraries->merge($this->sharers->flatMap(fn(/** @var Actor $sharer */ $sharer) => $sharer->getLibraries(true, false, true, false)));
        }

        // Libraries of children of this actor
        if ($children) {
            $libraries = $libraries->merge($this->children_libraries);
        }

        // Libraries of the parent of the user
//        if ($parent && ($this->details->parent_is_group ?? false)) {  --  WTF is this not working ?????
        if ($parent && ($this->parent->is_group ?? false)) {
            $libraries = $libraries->merge($this->parent->getLibraries(true, true, !$this->is_group && Gate::allows(\Neo\Enums\Capability::libraries_edit->value) && !$this->limited_access));
        }

        return $libraries->unique("id")->sortBy("name")->values();
    }

    /**
     * Give libraries directly owned by this actor
     *
     * @return Collection<Library>
     */
    protected function getOwnLibrariesAttribute(): Collection {
        return $this->getCachedRelation("own_libraries", fn() => Library::of($this)->get());
    }

    /**
     * @return Collection<Library>
     */
    protected function getChildrenLibrariesAttribute(): Collection {
        return $this->getCachedRelation("children_libraries", fn() => Library::ofChildrenOf($this)->get());
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

    public function getRegistrationSentAttribute(): bool {
        return $this->password !== null || $this->signupToken !== null;
    }

    public function getIsRegisteredAttribute(): bool {
        return $this->password !== null && $this->signupToken === null;
    }


    /*
    |--------------------------------------------------------------------------
    | Authentication Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @param bool $updateIfNecessary
     * @return bool
     */
    public function is2FAValid(bool $updateIfNecessary = true): bool {
        $token = $this->twoFactorToken;

        // Is there a token ?
        if ($token === null) {
            // If we are here, it means the user is logged in using its identifier, but has no twoFA.
            // We create one for it
            if ($updateIfNecessary) {
                $token = new TwoFactorToken();
                $token->actor()->associate($this);
                $token->save();
            }
            return false;
        }

        // If the token is not validated and is too old, recreate one and stop here
        if (!$token->validated && $token->created_at->diffInMinutes(Date::now()) >= 15) {
            TwoFactorToken::query()
                          ->where("actor_id", "=", $this->id)
                          ->delete();

            if ($updateIfNecessary) {
                $token = new TwoFactorToken();
                $token->actor()->associate($this);
                $token->save();
            }

            return false;
        }

        // If the token is not validated and is not too old, stops here just saying not validated
        if (!$token->validated) {
            return false;
        }

        // If the token is validated but is too old, create a new one and say not validated
        if ($token->validated_at->diffInMonths(Date::now()) >= 1) {
            $token->delete();

            if ($updateIfNecessary) {
                $token = new TwoFactorToken();
                $token->actor()->associate($this);
                $token->save();
            }

            return false;
        }

        // token is validated and is not expired
        return true;
    }

    /**
     * Build and return a JWT for the current user.
     *
     * @param bool $isImpersonating
     * @return string
     */
    public function getJWT(bool $isImpersonating = false): string {
        $twoFAIsValid = $this->is2FAValid(!$isImpersonating);

        // If this token is for impersonating OR if the user hasn't finished all auth steps, the token should expire in the 24hrs, otherwise, it expires one month after the second FA has been done
        $expire = $isImpersonating || !$twoFAIsValid ? Date::now()
                                                           ->addDay()->timestamp : $this->twoFactorToken->validated_at->addMonth()->timestamp;

        $payload = [
            // Registered
            "iss"  => config("app.url"),
            "aud"  => "*.neo-ooh.com",
            "iat"  => time(),
            "exp"  => $expire,

            // Private
            "uid"  => $this->id,            // uid => user id
            "name" => $this->name,          //
            "2fa"  => $twoFAIsValid,        // 2fa => Two Factor Auth
            "tos"  => $this->tos_accepted,  // tos => Terms of Use
        ];

        // When impersonating someone, we add additional fields to the token to ensure it can only be used by the current actor
        if ($isImpersonating) {
            $payload["imp"] = true;         // imp => Impersonating
            $payload["iid"] = Auth::id();   // iid => Impersonator Id
        }

        return JWT::encode($payload, config("auth.jwt_private_key"), "RS256");
    }

    public function getCampaignsStatusAttribute(): CampaignStatus|null {
        /** @var Collection<CampaignStatus> $campaignsStatus */
        $campaignsStatus = $this->getCampaigns(true, false, false, false)
                                ->load("schedules")
                                ->pluck("status")
                                ->values();

        if ($campaignsStatus->count() === 0) {
            return null;
        }

        if ($campaignsStatus->contains(CampaignStatus::Pending)) {
            return CampaignStatus::Pending;
        }

        if ($campaignsStatus->contains(CampaignStatus::Offline) || $campaignsStatus->contains(CampaignStatus::Empty)) {
            return CampaignStatus::Offline;
        }

        if ($campaignsStatus->contains(CampaignStatus::Live)) {
            return CampaignStatus::Live;
        }

        if ($campaignsStatus->contains(CampaignStatus::Expired)) {
            return CampaignStatus::Expired;
        }

        return CampaignStatus::Offline;
    }

    /**
     * [year => [month => traffic]]
     *
     * @return array<int, array<int>>|null
     */
    public function getCompoundTrafficAttribute(): array|null {
        if ($this->is_property) {
            return $this
                ->property
                ->traffic
                ->data
                ->groupBy(["year", "month"])
                ->map(fn($yearData) => $yearData->map(fn($monthData) => $monthData->map(fn($d) => $d->traffic ?? $d->temporary)
                                                                                  ->sum()))
                ->toArray();
        }

        if (!$this->is_group) {
            return null;
        }

        $children = $this->selectActors()->directChildren()->where("is_group", "=", true)->get();

        $childrenData = $children->map(fn($child) => $child->compound_traffic);

        $trafficValues = new Collection();

        foreach ($childrenData as $dataset) {
            foreach ($dataset as $year => $yearValues) {
                if (!$trafficValues->has($year)) {
                    $trafficValues[$year] = new Collection([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);
                }

                $v = $trafficValues[$year];
                foreach ($yearValues as $monthIndex => $traffic) {
                    $v[$monthIndex] += $traffic;
                }
                $trafficValues[$year] = $v;
            }
        }

        return $trafficValues->toArray();
    }
}
