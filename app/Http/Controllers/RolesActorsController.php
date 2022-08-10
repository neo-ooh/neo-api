<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RolesActorsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\RolesActors\DestroyRoleActorRequest;
use Neo\Http\Requests\RolesActors\StoreRoleActorRequest;
use Neo\Models\Actor;
use Neo\Models\Role;

class RolesActorsController extends Controller {
    public function index(Role $role): Response {
        Gate::authorize(Capability::roles_edit->value);

        return new Response($role->actors);
    }

    public function store(StoreRoleActorRequest $request, Role $role): Response {
        /** @var Actor $actor */
        $actor = Actor::query()->find($request->validated()["actor_id"]);

        if ($role->actors->pluck('id')->contains($actor->id)) {
            return new Response([
                "code"    => "roles.not-allowed",
                "message" => "User already has this role",
            ],
                403);
        }

        $role->actors()->attach($actor);
        $role->refresh();

        return new Response($role->actors);
    }

    public function destroy(DestroyRoleActorRequest $request, Role $role): Response {
        $actor = Actor::query()->find($request->validated()["actor_id"]);

        if (!$role->actors->pluck('id')->contains($actor->id)) {
            return new Response([
                "code"    => "roles.not-allowed",
                "message" => "User does not have role",
            ],
                403);
        }

        $role->actors()->detach($actor);
        $role->refresh();

        return new Response($role->actors);
    }
}
