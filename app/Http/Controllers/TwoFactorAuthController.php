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

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Auth\TwoFactorValidationRequest;
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

    public function test(): Response {
        return new Response([
            "name"  => Auth::user()->name,
            "email" => Auth::user()->email,
        ]);
    }
}
