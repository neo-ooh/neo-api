<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WelcomeController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\SignupTokens\CheckSignupTokenRequest;
use Neo\Http\Requests\SignupTokens\SetNewAccountPasswordRequest;
use Neo\Models\SignupToken;

class WelcomeController extends Controller {
    public function check(CheckSignupTokenRequest $request): Response {
        $rawToken = $request->input("token");

        //Check if token exists
        $token = SignupToken::query()->where("token", $rawToken)->first();

        if (is_null($token)) {
            return new Response([
                "code"    => "welcome.bad-token",
                "message" => "The provided token is invalid.",
            ],
                400);
        }

        // Token is valid, return a success with the name of the associated user
        return new Response([
            "name" => $token->actor->name,
            // Not using the dynamic attribute here as it has the same name
            // as the attribute
        ]);
    }

    public function setPassword(SetNewAccountPasswordRequest $request): Response {
        // Token existence is validated by the request, just set the password
        /** @var SignupToken $token */
        $token = SignupToken::query()->where("token", $request->validated()["token"])->first();

        $actor           = $token->actor;
        $actor->password = $request->validated()["password"];
        $actor->save();

        // Delete the signup token
        $token->delete();

        return new Response([]);
    }

}
