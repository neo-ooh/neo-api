<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoginController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Neo\Http\Requests\Auth\LoginRequest;
use Neo\Models\Actor;

class LoginController extends Controller {
    /**
     * The login method validates the provided email and password against the users database.
     * In case a correct match is found, a JWT is provided for further requests as the user.
     *
     * @param LoginRequest $request
     * @return Response
     */
    public function login(LoginRequest $request): Response {
        // Get the submitted credentials.
        $credentials = $request->validated();

        // Check if a user with the provided email exist in the database
        /** @var Actor $actor */
        $actor = Actor::query()->where('email', $credentials['email'])->first();

        if (!$actor) {
            // User does not exists
            return new Response([
                "code"    => "auth.bad-email",
                "message" => "User does not exists",
                "errors"  => ["email" => ["auth.bad-email"]],
            ], 422);
        }

        // Make sure the user is allowed to log in
        if ($actor->password === '' || $actor->is_group || $actor->is_locked || !is_null($actor->signupToken)) {
            // This user cannot be used directly.
            return new Response([
                "code"    => "auth.not-allowed",
                "message" => "This user cannot be used",
                "errors"  => ["email" => ["This user cannot be used"]],
            ],
                422);
        }

        if (!Hash::check($credentials['password'], $actor->password)) {
            // Bad password
            return new Response([
                "code"    => "auth.bad-password",
                "message" => "Password mismatch",
                "errors"  => ["password" => ["Password mismatch"]],
            ], 422);
        }

        // Credentials are ok.

        // Log the user inside Laravel
        Auth::setUser($actor);

        // Update the user login date
        $actor->last_login_at = $actor->freshTimestamp();
        $actor->save();

        // Returns the new jwt token for this user
        return new Response([
            "token"        => $actor->getJWT(),
            "tos_accepted" => $actor->tos_accepted,
        ]);
    }
}
