<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesContactsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Models\User;
use Neo\Modules\Properties\Http\Requests\PropertiesContacts\DeleteContactRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesContacts\ListContactsRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesContacts\StoreContactRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesContacts\UpdateContactRequest;
use Neo\Modules\Properties\Models\Property;

class PropertiesContactsController {
    public function show(ListContactsRequest $request, Property $property) {
        return new Response($property->contacts);
    }

    public function store(StoreContactRequest $request, Property $property) {
        $property->contacts()->attach($request->input("actor_id"), [
            "role" => $request->input("role") ?? "",
        ]);

        return new Response(["status" => "ok"], 201);
    }

    public function update(UpdateContactRequest $request, Property $property, User $user) {
        $property->contacts()->where("actor_id", "=", $user->getKey())
                 ->update(["role" => $request->input("role") ?? ""]);

        return new Response([], 200);
    }

    public function destroy(DeleteContactRequest $request, Property $property, User $user) {
        $property->contacts()->detach($user->getKey());
    }
}
