<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PasswordRecoveries\CheckRecoveryTokenRequest;
use Neo\Http\Requests\PasswordRecoveries\PasswordForgotRequest;
use Neo\Http\Requests\PasswordRecoveries\PasswordRecoveryRequest;
use Neo\Models\RecoveryToken;
use Neo\Models\Actor;

class PasswordRecoveryController extends Controller {
    public function makeToken (PasswordForgotRequest $request): Response {
        $email = $request->validated()["email"];

        // Check if a user match this email
        /** @var Actor $actor */
        $actor = Actor::query()->where('email', $email)->first();

        if (is_null($actor)) {
            return new Response([
                "code"    => "recovery.unknown-email",
                "message" => "Unknown email",
            ], 400);
        }

        // Is the user allowed to recover its password ?
        if ($actor->is_group || $actor->is_locked) {
            return new Response([
                "code"    => "recovery.unauthorized",
                "message" => "User is not authorized for password recovery",
            ], 401);
        }

        // Invalidate any previous password recovery tokens
        RecoveryToken::destroy($actor->email);

        // Create a new recovery token
        $recoveryToken = new RecoveryToken([ "email" => $actor->email ]);
        $recoveryToken->save(); // The email is sent to the user on token creation

        return new Response([]);
    }

    public function validateToken (CheckRecoveryTokenRequest $request): Response {
        $token = $request->input('token');

        // Check the token is valid
        /** @var RecoveryToken $recoveryToken */
        $recoveryToken = RecoveryToken::query()->where("token", $token)->first();

        if (is_null($recoveryToken)) {
            return new Response([
                "code"    => "recovery.bad-token",
                "message" => "Invalid recovery token",
            ], 400);
        }


        // Token is valid, return user name for display purpose a success status
        return new Response([
            "name" => $recoveryToken->actor->name,
        ]);
    }


    public function resetPassword (PasswordRecoveryRequest $request): Response {
        $token = $request->input('token');
        $password = $request->input('password');

        // Check the token is valid
        /** @var RecoveryToken $recoveryToken */
        $recoveryToken = RecoveryToken::query()->where("token", $token)->first();

        if (is_null($recoveryToken)) {
            return new Response([
                "code"    => "recovery.bad-token",
                "message" => "Invalid recovery token",
            ], 400);
        }

        // Token is correct, update password
        $actor = $recoveryToken->actor;
        $actor->password = $password;
        $actor->save();

        // And erase the token
        RecoveryToken::destroy($actor->email);

        return new Response([]);
    }
}
