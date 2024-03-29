<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessTokenGuard.php
 */

namespace Neo\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Request;
use Neo\Models\AccessToken;

/**
 * This guard is used to authenticate third-party services wanting to connect to the API.
 * External access is made with the use of a token who is NOT a JWT.
 * Endpoint accessible by third-party services are different from the one used by regular users.
 *
 * @package NeoServices\Auth
 */
class AccessTokenGuard implements Guard {

    /**
     * @var AccessToken|null Will store the AccessToken once validated
     */
    protected ?AccessToken $token = null;

    /**
     * JwtGuard constructor.
     */
    public function __construct() {
        // Get the token from the request
        $requestToken = Request::bearerToken() ?? Request::input("apiKey");

        // Do we have a token ?
        if (is_null($requestToken)) {
            // No
            return;
        }

        // We have a token, validate its existence/validity
        /** @var ?AccessToken $token */
        $token = AccessToken::query()
                            ->where("token", "=", $requestToken)
                            ->first();

        if ($token === null) {
            // No token matching the given one
            return;
        }

        // Token do exist, store it
        $this->token = $token;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool {
        return !is_null($this->token);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool {
        // A guest is everything but a user
        return !$this->check();
    }

    public function hasUser(): bool {
        return $this->user() !== null;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return AccessToken|null
     */
    public function user(): AccessToken|null {
        return $this->token;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id(): ?int {
        if (is_null($this->user())) {
            return null;
        }

        return $this->token->id;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []): bool {
        /** @var ?AccessToken $token */
        $token = AccessToken::query()
                            ->where("token", "=", $credentials["token"])
                            ->first();

        if (is_null($token)) {
            return false;
        }

        $this->setUser($token);

        return true;
    }

    /**
     * Set the current actor.
     *
     * @param Authenticatable|null $token
     *
     * @return void
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setUser(?Authenticatable $token): void {
        $this->token = $token;

        if ($this->token) {
            // Update the token last used at property
            $this->token->last_used_at = $this->token->freshTimestamp();
            $this->token->save();
        }
    }
}
