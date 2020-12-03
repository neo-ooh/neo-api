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
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Roles\StoreRoleRequest;
use Neo\Http\Requests\Roles\UpdateRoleRequest;
use Neo\Models\Role;

class RolesController extends Controller {
    public function index (): Response {
        Gate::authorize(Capability::roles_edit);

        return new Response(Role::all());
    }

    public function store (StoreRoleRequest $request): Response {
        $values = $request->validated();

        $role = new Role();
        $role->name = $values["name"];
        $role->desc = $values["desc"];
        $role->save();

        $role->capabilities()->attach($values["capabilities"]);

        return new Response($role, 201);
    }

    public function show (Role $role): Response {
        Gate::authorize(Capability::roles_edit());

        return new Response($role->loadMissing([ "capabilities", "actors" ]));
    }

    public function update (UpdateRoleRequest $request, Role $role): Response {
        $values = $request->validated();

        $role->name = $values["name"];
        $role->desc = $values["desc"];
        $role->save();

        return new Response($role->loadMissing([ "capabilities", "actors" ]));
    }

    public function destroy (Role $role): Response {
        Gate::authorize(Capability::roles_edit());

        $role->delete();

        return new Response([]);
    }
}
