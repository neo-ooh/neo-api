<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RolesCapabilitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Roles\DeleteRoleCapabilityRequest;
use Neo\Http\Requests\Roles\StoreRoleCapabilityRequest;
use Neo\Http\Requests\Roles\UpdateRoleCapabilityRequest;
use Neo\Models\Role;

class RolesCapabilitiesController extends Controller {
    public function index(Role $role): Response {
        Gate::authorize(Capability::roles_edit->value);

        return new Response($role->capabilities);
    }

    public function store(StoreRoleCapabilityRequest $request, Role $role): Response {
        $role->capabilities()->attach($request->validated()["capability"]);
        $role->refresh();

        return new Response($role->capabilities);
    }

    public function update(UpdateRoleCapabilityRequest $request, Role $role): Response {
        $role->capabilities()->sync($request->validated()['capabilities']);
        $role->refresh();

        return new Response($role->capabilities);
    }

    public function destroy(DeleteRoleCapabilityRequest $request, Role $role): Response {
        if (!$role->capabilities->pluck('id')->contains($request->validated()["capability"])) {
            return new Response([
                "code"    => "roles.not-removable",
                "message" => "The role does not have this capability",
            ], 403);
        }

        $role->capabilities()->detach($request->validated()["capability"]);

        return new Response($role->capabilities);
    }
}
