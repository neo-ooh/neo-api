<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TwoFactorAuthController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Actors\RecycleTwoFARequest;
use Neo\Http\Requests\Actors\ValidateTwoFaRequest;
use Neo\Http\Requests\Auth\TwoFactorValidationRequest;
use Neo\Models\Actor;
use Neo\Models\TwoFactorToken;

class TwoFactorAuthController extends Controller {
    public function validateToken(TwoFactorValidationRequest $request): Response {
        // Get the user token
        /**
         * @var TwoFactorToken
         */
        $token = Auth::user()->twoFactorToken;

        // There is no need to check the token format, it has already been validated by the `TwoFactorValidationRequest`

        // Try to validate the given token against the stored one
        if (!$token->validate($request->validated()['token'])) {
            // Given token is erroneous
            return new Response([
                "code"    => "auth.bad-2fa-token",
                "message" => "Provided two-factor token is invalid",
            ],
                403);
        }

        // Good token
        return new Response([
            "token" => Auth::user()->getJWT(),
        ]);
    }

    /**
     * Deletes the authentication second-step token and create a new one for the passed actor
     * @param RecycleTwoFARequest $request
     * @param Actor               $actor
     * @return Response
     */
    public function recycle(RecycleTwoFARequest $request, Actor $actor): Response {
        // Delete any Two Fa token of the user
        $actor->twoFactorToken()->delete();

        // Create a new one
        $token = new TwoFactorToken();
        $token->actor()->associate($actor);
        $token->save();

        // We're good, creating the new token has sent an email to the user
        $token->makeVisible("token");
        return new Response($token);
    }

    public function forceValidateToken(ValidateTwoFaRequest $request, Actor $actor): Response {
        $token = $actor->twoFactorToken;

        if(!$token) {
            // No token, do nothing
            return new Response([]);
        }

        $token->makeVisible("token");
        $token->validate($token->token);

        return new Response($token);
    }
}
