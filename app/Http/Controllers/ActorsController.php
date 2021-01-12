<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ActorsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Actors\DestroyActorsRequest;
use Neo\Http\Requests\Actors\ListActorsRequest;
use Neo\Http\Requests\Actors\RequestActorTokenRequest;
use Neo\Http\Requests\Actors\StoreActorRequest;
use Neo\Http\Requests\Actors\UpdateActorRequest;
use Neo\Jobs\CreateSignupToken;
use Neo\Jobs\CreateUserLibrary;
use Neo\Models\Actor;
use Neo\Models\SignupToken;

/**
 * Class ActorsController
 *
 * @package Neo\Http\Controllers
 */
class ActorsController extends Controller {
    public function index(ListActorsRequest $request): Response {
        $params = $request->validated();

        $query = Auth::user()->newQuery()->fromSub(Auth::user()->AccessibleActors(), 'acc_act')
                     ->orderBy("acc_act.name");

        // We return all actors by default. If a groups query parameter is specified, let it decide
        if ($request->has("groups")) {
            $query->where('acc_act.is_group', '=', (bool)$request->query('groups'));
        }

        // Exclude specific actors
        if ($request->has("exclude")) {
            $query->whereNotIn("acc_act.id", $params["exclude"]);
        }

        // Execute the query
        $children = $query->get();

        // If the user
        if ((bool)($params['withself'] ?? false)) {
            $children = $children->push(Auth::user());
        }

        return new Response($children->unique());
    }

    public function show(Request $request, Actor $actor): Response {
        $with = $request->get("with", []);

        if (in_array("capabilities", $with, true)) {
            $actor->append("capabilities");
        }

        if (in_array("standalone_capabilities", $with, true)) {
            $actor->append("standalone_capabilities");
        }

        if (in_array("branding", $with, true) && !is_null($actor->branding_id)) {
            $actor->load("branding");
        }

        if (in_array("applied_branding", $with, true)) {
            $actor->append("applied_branding");
        }

        if (in_array("roles", $with, true)) {
            $actor->append("roles");
            $actor->append("own_roles");
        }

        if (in_array("own_locations", $with, true)) {
            $actor->load("own_locations");
        }

        if (in_array("direct_children", $with, true)) {
            $actor->append("direct_children");
        }

        if (!$actor->is_group) {
            $actor->load("signupToken");
        }

        return new Response($actor->withDetails());
    }

    public function store(StoreActorRequest $request): Response {
        // Authorization is handled by the request
        $values = $request->validated();

        // Create the actor's "shell"
        $actor           = new Actor();
        $actor->name     = $values['name'];
        $actor->is_group = $values['is_group'];
        $actor->save();

        // Place it in the tree
        /** @var Actor $parent */
        $parent = Actor::query()->find($values["parent_id"]);
        $actor->moveTo($parent);

        // If its a physical user, add more informations
        if (!$actor->is_group) {
            $actor->email       = $values['email'];
            $actor->branding_id = $values['branding_id'];
            $actor->addRoles($values['roles']);
            $actor->addCapabilities($values['capabilities']);
            $actor->save();

            // Execute the user's creation side effects
            CreateSignupToken::dispatch($actor->id);
            CreateUserLibrary::dispatch($actor->id);
        }

        return new Response($actor->withDetails(), 201);
    }

    public function update(UpdateActorRequest $request, Actor $actor): Response {
        // Since all request properties are optional, make sure at least one was given
        if (count($request->all()) === 0) {
            return new Response([
                "code"    => "empty-request",
                "message" => "You must pass at lease 1 parameter when calling this route"
            ], 422);
        }

        // The request handles input validation
        $actor->name        = $request->get("name", $actor->name);
        $actor->email       = $request->get("email", $actor->email);
        $actor->branding_id = $request->get("branding_id", $actor->branding_id);

        if ($request->has("password")) {
            $actor->password = $request->get("password");
        }

        $lock = $request->get("is_locked", $actor->is_locked);

        if ($lock !== $actor->is_locked) {
            $actor->is_locked = $lock;
            $actor->locked_by = $lock ? Auth::id() : null;
        }

        $actor->save();

        // If a parent_id is present and different from the current actor's parent, try to move the actor
        if (($parentID = $request->get("parent_id", false)) && $parentID !== $actor->parent_id) {
            /** @var Actor $parent */
            $parent = Actor::query()->findOrFail($parentID);

            // Make sure we are not creating a hierarchical loop.
            // A user cannot have one of its child or itself has its parent
            if ($parent->id === $actor->id || $actor->isParentOf($parent)) {
                return new Response([
                    'code'    => 'actor.hierarchy-loop',
                    'message' => 'Parent assignment would result in incoherent actors hierarchy',
                    'data'    => $actor,
                ], 403);
            }

            $actor->moveTo($parent);
        }

        $actor->unsetRelations();
        $actor->refresh();

        return new Response($this->show($request, $actor)->original);
    }

    public function destroy(DestroyActorsRequest $request, Actor $actor): Response {
        // Move or delete this actor's resources according to specified behaviour
        switch ($request["behaviour"]) {
            case "to-parent":
                $actor->children->each(fn($actor) => $actor->moveTo($actor->parent));
                break;
            case "to-self":
                $actor->children->each(fn($actor) => $actor->moveTo(Auth::user()));
                break;
            default: // case "to_parent":
                $actor->children->each(fn($child) => $child->delete());
                break;
        }

        $actor->delete();

        return new Response([]);
    }

    public function resendWelcomeEmail(Actor $actor): Response {
        // Remove leftover token
        SignupToken::query()->where("actor_id", "=", $actor->id)->delete();

        CreateSignupToken::dispatch($actor->id);

        return new Response();
    }

    public function getToken(RequestActorTokenRequest $request) {
        return new Response(["token" => Auth::user()->getJWT()]);
    }
}
