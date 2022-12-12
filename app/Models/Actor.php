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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Neo\Enums\ActorType;
use Neo\Models\Traits\HasCampaigns;
use Neo\Models\Traits\HasCapabilities;
use Neo\Models\Traits\HasHierarchy;
use Neo\Models\Traits\HasLocations;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Models\Traits\HasRoles;
use Neo\Models\Traits\WithRelationCaching;
use Neo\Models\Utils\ActorsGetter;
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
 * @property ActorType           $type
 * @property bool                $is_group              Tell if the current actor is a group
 * @property bool                $is_property           Tell if the current actor is a property. A property is always  group,
 *           never a user.
 * @property bool                $is_locked             Tell if the current actor has been locked. A locked actor cannot login
 * @property int|null            $locked_by             Tell who locke this actor, if applicable
 * @property int|null            $branding_id           ID of the branding applied to this user
 *
 * @property ActorDetails        $details               Meta information about the actor
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
 * @property Property|null       $property
 *
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
 * @property Collection<Library> $libraries
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
    use HasLocations;
    use HasRoles;
    use HasHierarchy;
    use HasCampaigns;
    use HasCapabilities;
    use WithRelationCaching;
    use HasPublicRelations;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "actors";

    /**
     * The attributes that should not be included in serialization
     *
     * @var list<string>
     */
    protected $hidden = [
        "password",
        "details",
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
        "last_login_at"  => "date",
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
        "type",
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleActor::class;

    protected function getPublicRelations() {
        return [
            "logo"          => "load:logo",
            "locations_ids" => "load:own_locations:id",
        ];
    }

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Actor $actor) {
            if ($actor->is_property) {
                $actor->property?->delete();
            }
        });
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
     * @return HasMany<Library>
     */
    public function libraries(): HasMany {
        return $this->hasMany(Library::class, "owner_id", "id");
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
     * @param bool $ids
     * @return \Illuminate\Support\Collection
     */
    public function getAccessibleActors(bool $children = true, bool $shallow = false, bool $shared = true, bool $parent = true, bool $ids = false): \Illuminate\Support\Collection {
        $getter = ActorsGetter::from($this);

        if (!$this->limited_access) {
            $getter->selectSiblings(!$shallow);
        }

        if ($children) {
            $getter->selectChildren(!$shallow);
        }

        if ($shared) {
            $getter->selectShared(!$shallow);
        }

        if ($parent && !$this->limited_access) {
            $getter->selectParent();
        }

        return $ids ? $getter->getSelection() : $getter->getActors();
    }

    public function getParentIdAttribute(): ?int {
        return $this->details->parent_id;
    }

    public function getParentIsGroupAttribute(): bool {
        return $this->details->parent_is_group;
    }

    public function getIsPropertyAttribute(): bool {
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
        // We are not a parent of the given actor, is it shared with us or part of our parent hierarchy if its a group ?
        return $this->getAccessibleActors(ids: true)->contains($node->id);
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

    public function getTypeAttribute(): ActorType {
        if ($this->is_property) {
            return ActorType::Property;
        }

        if ($this->is_group) {
            return ActorType::Group;
        }

        return ActorType::User;
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

    /**
     * [year => [month => traffic]]
     *
     * @return array<int, array<int>>|null
     */
    public function getCompoundTrafficAttribute(): array|null {
        if (!in_array($this->type, [ActorType::Group, ActorType::Property], true)) {
            return null;
        }

        if ($this->type === ActorType::Property) {
            return $this
                ->property
                ->traffic
                ->data
                ->groupBy(["year", "month"])
                ->map(fn($yearData) => $yearData->map(fn($monthData) => $monthData->map(fn($d) => $d->traffic ?? $d->temporary)
                                                                                  ->sum()))
                ->toArray();
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
