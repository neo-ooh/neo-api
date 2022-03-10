<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsPhoneController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsPhones\DestroyPhoneRequest;
use Neo\Http\Requests\ActorsPhones\StorePhoneRequest;
use Neo\Models\Actor;
use Neo\Models\Phone;

class ActorsPhoneController {
    public function store(StorePhoneRequest $request, Actor $actor) {
        $phone                 = $actor->phone ?: new Phone();
        $phone->number_country = $request->input("phone_country");
        $phone->number         = $request->input("phone");
        $phone->save();

        $actor->phone()->associate($phone);
        $actor->save();

        return new Response($phone, 200);
    }

    public function destroy(DestroyPhoneRequest $request, Actor $actor) {
        $actor->phone?->delete();

        // Make the sure the auth method stays valid
        $actor->two_fa_method = 'email';
        $actor->save();

        return new Response(null, 202);
    }
}
