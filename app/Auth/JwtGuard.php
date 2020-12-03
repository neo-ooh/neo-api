<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Auth;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Request;
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
class JwtGuard implements Guard {

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

        // Validate the token
        if (!$this->isTokenValid()) {
            // Invalid token
            return;
        }

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

        // Token is valid, use its `uid` property to get the matching user
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

    private function isTokenValid (): bool {
        if (is_null($this->token)) {
            return false;
        }

        // A token is valid if its 2fa and tos properties are true
        return $this->token['2fa'] === true && $this->token['tos'] === true;
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
     * @return int|string|null
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
     * @param Authenticatable $actor
     *
     * @return void
     */
    public function setUser (?Authenticatable $actor): void {
        /* Authenticatable => Actor */
        $this->actor = $actor;
    }
}
