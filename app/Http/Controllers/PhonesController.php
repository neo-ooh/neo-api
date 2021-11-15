<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PhonesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Phones\DestroyPhoneRequest;
use Neo\Models\Actor;
use Neo\Models\Phone;

class PhonesController {
    public function destroy(DestroyPhoneRequest $request, Phone $phone) {
        /** @var Actor|null $actor */
        $actor = Actor::query()->where("phone_id", "=", $phone->id)->first();

        if (!$actor) {
            $phone->delete();
            return new Response(null, 202);
        }

        if ($actor->isNot(Auth::user()) && !Auth::user()->hasAccessTo($actor)) {
            return new Response("Cannot remove phone of inaccessible user", 400);
        }

        $phone->delete();

        $actor->two_fa_method = 'email';
        $actor->save();

        return new Response(null, 202);
    }
}
