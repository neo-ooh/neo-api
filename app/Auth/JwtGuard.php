<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - JwtGuard.php
 */

namespace Neo\Auth;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Neo\Enums\Capability;
use Neo\Models\Actor;

/**
 * This guard is used to authenticate the current user using its Authentication token.
 * This class is eager loading, and will try to fetch and store the user model directly from the constructor.
 * This is to ensure maximum performance on further access.
 *
 * For a user to be authenticated, it hase to have a valid JWT Token, with its 2fa and tos properties set to true.
 * If one of those properties is false, or if the token is missing, the user is not allowed to perform any request on
 * this api
 *
 * @package NeoServices\Auth
 */
abstract class JwtGuard implements Guard {

    /**
     * @var Actor|null Will store the user once it has been loaded at least once
     */
    protected ?Actor $actor = null;

    /**
     * Holds the decoded token.
     *
     * @var array|null
     */
    protected ?array $token = null;

    /**
     * @var UserProvider
     */
    protected UserProvider $provider;

    /**
     * Specify if a user who has not validated its two factor authentication is allowed by the guard
     * @var bool
     */
    protected bool $allowNonValidated2FA;

    /** Specify if a user who has not approved the terms of service is allowed by the guard
     * @var bool
     */
    protected bool $allowNonApprovedTos;

    /**
     * Specify if a user who's account is disabled is allowed by the guard
     * @var bool
     */
    protected bool $allowDisabledAccount;


    /**
     * JwtGuard constructor.
     *
     * @param UserProvider $actorProvider
     */
    public function __construct (UserProvider $actorProvider) {
        $this->provider = $actorProvider;
        $this->token = $this->getToken();

        // Try to grab and store the user
        // Do we have a token ?
        if (is_null($this->token)) {
            // No
            return;
        }

        // Token is valid, use its `uid` property to get the matching user
        // Get the user
        /** @var Actor $actor */
        $actor = Actor::query()->find($this->token['uid']);

        if (is_null($actor)) {
            return; // Bad user
        }

        // Make sure a group is not getting logged in
        if($actor->is_group) {
            return;
        }

        // Validate the token
        if (!$this->validateUser($actor)) {
            // Invalid token
            return;
        }

        $this->setUser($actor);
    }

    /**
     * Retrieve and decode the token.
     *
     * @return array|null
     */
    protected function getToken (): ?array {
        // Get the Authorization/Bearer token
        $token = Request::bearerToken();

        // Try to decode the token
        try {
            $data = JWT::decode($token, config('auth.jwt_public_key'), [ 'RS256' ]);
        } catch (Exception $ex) {
            // Invalid token, this is not a user
            return null;
        }

        return (array)$data;
    }

    private function validateUser(Actor $actor): bool {
        // If its an impersonating token, we need to validate its accompanying token
        $isImpersonating = array_key_exists("imp", $this->token) && $this->token["imp"];

        if($isImpersonating && !$this->validateImpersonator()) {
            // We could not validate the impersonator, reject the auth
            return false;
        }

        if($isImpersonating) {
            return true;
        }

        return $this->checkActorMeetsCriteria($actor);
    }

    public function checkActorMeetsCriteria(Actor $actor): bool {
        // Validate that the token has its two factor auth OR that the guard allows it to be missing
        if(!$this->token['2fa'] && !$this->allowNonValidated2FA) {
            return false;
        }

        // Validate that the user has approved the Tos
        if(!$actor->tos_accepted && !$this->allowNonApprovedTos) {
            return false;
        }

        // Validate the the user account is not locked, OR that a locked account is allowed to log in
        if($actor->is_locked && !$this->allowDisabledAccount) {
            return false;
        }

        return true;
    }

    /**
     * Validate the existence of a second Authorization token validating the use of the main token for impersonation.
     * @return bool
     */
    protected function validateImpersonator(): bool {
        $impersonatorToken = Str::substr(Request::header('X-Impersonator-Authorization', ''), strlen("Bearer "));

        try {
            $impersonatorData = (array)JWT::decode($impersonatorToken, config('auth.jwt_public_key'), ['RS256']);
        } catch(Exception $e) {
            return false;
        }

        $impersonator = Actor::findOrFail($impersonatorData["uid"]);

        // Validate the impersonator and make sure it has the capability to impersonate
        if(!$this->checkActorMeetsCriteria($impersonator) || !$impersonator->hasCapability(Capability::actors_impersonate())) {
            return false;
        }

        // Given token is valid, validate our main token is correctly associated with the current impersonatore
        return $this->token["iid"] === $impersonatorData["uid"];
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check (): bool {
        return !is_null($this->actor);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest (): bool {
        // A guest is everything but a user
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|Actor|null
     */
    public function user () {
        return $this->actor;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id () {
        if (is_null($this->user())) {
            return null;
        }

        return $this->actor->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate (array $credentials = []): bool {
        $actor = $this->provider->retrieveByCredentials($credentials);

        if (is_null($actor)) {
            return false;
        }

        $this->setUser($actor);

        return true;
    }

    /**
     * Set the current actor.
     *
     * @param Authenticatable|null $user
     *
     * @return void
     */
    public function setUser (?Authenticatable $user): void {
        /* Authenticatable => Actor */
        $this->actor = $user;
    }
}
